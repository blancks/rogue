<?php

declare(strict_types=1);

namespace Mantle\Loggers\Monolog;

use LogicException;
use Monolog\Handler\FilterHandler as MonologFilterHandler;
use Monolog\Handler\HandlerInterface as MonologHandlerInterface;
use Monolog\Processor\ProcessorInterface as MonologProcessorInterface;
use Monolog\Logger as MonologLogger;
use Monolog\Level as MonologLevel;
use Monolog\LogRecord;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Mantle\Contracts\LoggerHandlerInterface;
use Mantle\Contracts\LoggerInterface;
use Mantle\Contracts\LoggerProcessorInterface;
use Mantle\Loggers\Monolog\Handlers\HandlerInterface;
use Mantle\Loggers\Monolog\Handlers\StreamHandler;
use Mantle\Loggers\Monolog\Processors\ProcessorInterface;
use Throwable;
use ValueError;

/**
 * Multi-channel logger implementation using Monolog
 *
 * This logger manages multiple logging channels, each with their own handlers and processors.
 * It provides a convenient interface for creating and managing log channels with different
 * configurations while maintaining PSR-3 compliance through Monolog.
 *
 */
class Logger implements LoggerInterface
{
    /**
     * @var array<string, MonologLogger>
     */
    private array $loggers = [];

    /**
     * @var string
     */
    private string $defaultChannel;

    /** @var array<HandlerInterface> */
    private array $defaultHandlers;

    /** @var array<ProcessorInterface|callable(array<string,mixed>):array<string,mixed>> */
    private array $defaultProcessors;

    /**
     * Constructor
     *
     * @param string $defaultChannel Default 'app'. Case-insensitive, only allows following characters: [a-z0-9/_-]+
     * @param null|array<HandlerInterface> $defaultHandlers
     * @param null|array<ProcessorInterface|callable(array<string,mixed>):array<string,mixed>> $defaultProcessors
     * @throws ValueError when channel name contain invalid characters
     */
    public function __construct(
        string $defaultChannel = 'app',
        ?array $defaultHandlers = null,
        ?array $defaultProcessors = null
    ) {
        $this->defaultChannel = $this->validateChannelName($defaultChannel);
        $this->defaultHandlers = $defaultHandlers
            ?? [new StreamHandler(logsPath("/{$this->defaultChannel}.log"))];
        $this->defaultProcessors = $defaultProcessors ?? [];

        $this->register(
            $this->defaultChannel,
            $this->defaultHandlers,
            $this->defaultProcessors
        );
    }

    /**
     * Get a logger for a specific channel.
     * If the channel does not exist it will be created with the default handlers and processors
     *
     * @param string $channel Channel name. Case-insensitive, only allows following characters: [a-z0-9/_-]+
     * @return PsrLoggerInterface The logger for the specified channel
     * @throws ValueError when channel name contain invalid characters
     */
    public function channel(string $channel): PsrLoggerInterface
    {
        $channel = $this->validateChannelName($channel);

        if (!$this->hasChannel($channel)) {
            $this->register($channel, $this->defaultHandlers, $this->defaultProcessors);
        }

        return $this->loggers[$channel];
    }

    /**
     * Get the default channel
     *
     * @return PsrLoggerInterface
     */
    public function default(): PsrLoggerInterface
    {
        return $this->channel($this->defaultChannel);
    }

    /**
     * Register a new log channel with specific handlers and processors
     *
     * @param string $channel Channel name
     * @param array<HandlerInterface> $handlers
     * @param array<ProcessorInterface|callable(array<string,mixed>):array<string,mixed>> $processors
     * @return void
     */
    public function register(string $channel, array $handlers = [], array $processors = []): void
    {
        $channel = $this->validateChannelName($channel);
        $this->loggers[$channel] = new MonologLogger($channel);

        foreach ($handlers as $handler) {
            $this->addHandler($channel, $handler);
        }

        foreach ($processors as $processor) {
            $this->addProcessor($channel, $processor);
        }
    }

    /**
     * Add a handler to a specific channel
     *
     * @param string $channel Channel name
     * @param HandlerInterface $handler
     * @return void
     */
    public function addHandler(string $channel, LoggerHandlerInterface $handler): void
    {
        $this->assertExistingChannel($channel);
        $this->loggers[$channel]->pushHandler($handler->getHandler());
    }

    /**
     * Add a processor to a specific channel
     *
     * @param string $channel Channel name
     * @param ProcessorInterface|callable(array<string,mixed>):array<string,mixed> $processor
     * @return void
     */
    public function addProcessor(string $channel, LoggerProcessorInterface|callable $processor): void
    {
        $this->assertExistingChannel($channel);

        $isCallableProcessor = is_callable($processor);

        if (
            !$isCallableProcessor
            && !($processor instanceof ProcessorInterface)
        ) {
            throw new ValueError(
                'Invalid processor. Must provide an instance of ProcessorInterface'
            );
        }

        $discreteProcessor = $isCallableProcessor
            ? $processor
            : $processor->getProcessor();

        $this->loggers[$channel]->pushProcessor(
            $discreteProcessor instanceof MonologProcessorInterface
                ? $discreteProcessor
                : fn (LogRecord $record): LogRecord =>
                    $record->with($discreteProcessor($record->toArray()))
        );
    }

    /**
     * Check if a channel exists
     *
     * @param string $channel Channel name
     * @return bool True if the channel exists, false otherwise
     */
    public function hasChannel(string $channel): bool
    {
        return isset($this->loggers[$channel]);
    }

    /**
     * Set minimum logging level for a channel
     *
     * @param 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning' $level Minimum level to log. It is reccomended to use one of \Psr\Log\LogLevel consts
     * @param null|string $channel Channel name
     * @return void
     *
     * @throws ValueError when an invalid channel name is provided
     * @throws LogicException when the provided channel name is not registered
     *
     * @see \Psr\Log\LogLevel
     */
    public function setMinLevel(string $level, ?string $channel = null): void
    {
        $channel = isset($channel)
            ? $this->validateChannelName($channel)
            : $this->defaultChannel;

        $this->assertExistingChannel($channel);

        $logger = $this->loggers[$channel];
        $handlers = $logger->getHandlers();

        $logger->setHandlers([]);
        $levelEnum = $this->validateLevelName($level);

        foreach ($handlers as $handler) {
            $this->addHandlerWithLevel($channel, $handler, $levelEnum);
        }
    }

    /**
     * Assert that a channel exists, throwing an exception if it doesn't
     *
     * @param string $channel Channel name to check
     * @return void
     * @throws LogicException when the channel does not exist
     */
    private function assertExistingChannel(string $channel): void
    {
        if (!$this->hasChannel($channel)) {
            throw new LogicException('Unknown channel: '. $channel);
        }
    }

    /**
     * Validate and normalize channel name
     *
     * @param string $channel Channel name to validate
     * @return string Normalized channel name (lowercase)
     * @throws ValueError when channel name contains invalid characters
     */
    private function validateChannelName(string $channel): string
    {
        if (preg_match('#^[a-z0-9/_-]+$#i', $channel) === false) {
            throw new ValueError('Invalid channel name: '. $channel);
        }

        return strtolower($channel);
    }

    /**
     * Validate and convert level name to MonologLevel enum
     *
     * @param 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning' $level Level name to validate
     * @return MonologLevel The corresponding MonologLevel enum
     * @throws ValueError when an invalid level name is provided
     */
    private function validateLevelName(string $level): MonologLevel
    {
        try {

            return MonologLevel::fromName($level);

        } catch (Throwable $e) {

            throw new ValueError('Invalid level name: '. $level, previous: $e);

        }
    }

    /**
     * Helper method to add a handler with a specific level
     *
     * @param string $channel Channel name
     * @param MonologHandlerInterface $handler Original handler
     * @param MonologLevel $level New minimum level
     */
    private function addHandlerWithLevel(
        string $channel,
        MonologHandlerInterface $handler,
        MonologLevel $level
    ): void {
        $filterHandler = new MonologFilterHandler(
            $handler,
            $level,
            MonologLevel::Emergency
        );

        $this->loggers[$channel]->pushHandler($filterHandler);
    }
}
