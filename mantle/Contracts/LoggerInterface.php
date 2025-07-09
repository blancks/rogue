<?php

declare(strict_types=1);

namespace Rogue\Mantle\Contracts;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * Logger Interface
 *
 * This interface defines the contract for a multi-channel logging system.
 * It provides functionality to create, manage, and configure multiple logging
 * channels with different handlers and processors.
 *
 * The interface extends PSR-3 logging capabilities by adding channel-based
 * logging, custom handlers, and processors for log enrichment.
 *
 */
interface LoggerInterface
{
    /**
     * Get a logger for a specific channel
     *
     * @param string $channel Channel name
     * @return \Psr\Log\LoggerInterface
     */
    public function channel(string $channel): PsrLoggerInterface;

    /**
     * Get the default channel
     *
     * @return PsrLoggerInterface
     */
    public function default(): PsrLoggerInterface;

    /**
     * Register a new log channel with specific handlers
     *
     * @param string $channel Channel name
     * @param array<LoggerHandlerInterface> $handlers
     * @param array<LoggerProcessorInterface|callable(array<string,mixed>):array<string,mixed>> $processors
     * @return void
     */
    public function register(string $channel, array $handlers = [], array $processors = []): void;

    /**
     * Add a handler to a specific channel
     *
     * @param string $channel Channel name
     * @param LoggerHandlerInterface $handler
     * @return void
     */
    public function addHandler(string $channel, LoggerHandlerInterface $handler): void;

    /**
     * Add a processor to a specific channel
     *
     * @param string $channel Channel name
     * @param LoggerProcessorInterface|callable(array<string,mixed>):array<string,mixed> $processor
     * @return void
     */
    public function addProcessor(string $channel, LoggerProcessorInterface|callable $processor): void;

    /**
     * Check if a channel exists
     *
     * @param string $channel Channel name
     * @return bool
     */
    public function hasChannel(string $channel): bool;

    /**
     * Set minimum logging level for a channel
     *
     * @param string $level Minimum level to log
     * @param null|string $channel Channel name
     */
    public function setMinLevel(string $level, ?string $channel = null): void;
}
