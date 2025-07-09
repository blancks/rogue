<?php

declare(strict_types=1);

namespace Mantle\Aspects;

use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Mantle\Contracts\LoggerHandlerInterface;
use Mantle\Contracts\LoggerInterface;
use Mantle\Contracts\LoggerProcessorInterface;
use Mantle\Contracts\Traits\SingletonTrait;

/**
 * Logger Aspect
 *
 * Static facade for interacting with a PSR-3 logger.
 * Provides methods to set, retrieve, and proxy logging calls
 * via a singleton logger instance.
 */
final class Logger
{
    use SingletonTrait;

    private static LoggerInterface $instance;

    /**
     * Set the logger implementation to use.
     *
     * @param LoggerInterface $logger
     * @return void
     */
    public static function setInstance(LoggerInterface $logger): void
    {
        static::$instance = $logger;
    }

    /**
     * Get the singleton logger instance.
     */
    public static function getInstance(): LoggerInterface
    {
        return static::$instance;
    }

    /**
     * Get a logger for a specific channel
     *
     * @param string $channel Channel name
     * @return \Psr\Log\LoggerInterface
     */
    public static function channel(string $channel): PsrLoggerInterface
    {
        return static::$instance->channel($channel);
    }

    /**
     * Get the default channel
     *
     * @return PsrLoggerInterface
     */
    public static function default(): PsrLoggerInterface
    {
        return static::$instance->default();
    }

    /**
     * Register a new log channel with specific handlers
     *
     * @param string $channel Channel name
     * @param array<LoggerHandlerInterface> $handlers
     * @param array<LoggerProcessorInterface|callable(array<string,mixed>):array<string,mixed>> $processors
     * @return void
     */
    public static function register(string $channel, array $handlers = [], array $processors = []): void
    {
        static::$instance->register($channel, $handlers, $processors);
    }

    /**
     * Add a handler to a specific channel
     *
     * @param string $channel Channel name
     * @param LoggerHandlerInterface $handler
     * @return void
     */
    public static function addHandler(string $channel, LoggerHandlerInterface $handler): void
    {
        static::$instance->addHandler($channel, $handler);
    }

    /**
     * Add a processor to a specific channel
     *
     * @param string $channel Channel name
     * @param LoggerProcessorInterface|callable(array<string,mixed>):array<string,mixed> $processor
     * @return void
     */
    public static function addProcessor(string $channel, LoggerProcessorInterface|callable $processor): void
    {
        static::$instance->addProcessor($channel, $processor);
    }

    /**
     * Check if a channel exists
     *
     * @param string $channel Channel name
     * @return bool
     */
    public static function hasChannel(string $channel): bool
    {
        return static::$instance->hasChannel($channel);
    }

    /**
     * Set the minimum log level for logging.
     *
     * Only messages at this level or higher severity will be logged.
     *
     * Available log levels (from lowest to highest severity):
     * - debug     - Detailed debug information.
     * - info      - Interesting events, such as user logins or SQL logs.
     * - notice    - Normal but significant events.
     * - warning   - Exceptional occurrences that are not errors.
     * - error     - Runtime errors that do not require immediate action but should be monitored.
     * - critical  - Critical conditions, such as application component unavailability.
     * - alert     - Action must be taken immediately.
     * - emergency - System is unusable.
     *
     * @param string $level The minimum log level to allow.
     * @param null|string $channel Channel name
     * @return void
     */
    public static function setMinLevel(string $level, ?string $channel = null): void
    {
        static::$instance->setMinLevel($level, $channel);
    }

    /**
     * Log a message with the given level.
     *
     * This method allows you to log messages with any PSR-3 compliant log level.
     * The message will be sent to the default logging channel.
     *
     * @param string $level The log level (debug, info, notice, warning, error, critical, alert, emergency)
     * @param string $message The log message
     * @param array<mixed, mixed> $context Additional context data to include with the log entry
     * @return void
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        static::getInstance()->default()->log($level, $message, $context);
    }

    /**
     * Log an informational message.
     *
     * Use this method to log general information about application flow,
     * user actions, or other notable events that are useful for monitoring
     * and debugging purposes.
     *
     * @param string $message The informational message to log
     * @param array<mixed, mixed> $context Additional context data to include with the log entry
     * @return void
     */
    public static function info(string $message, array $context = []): void
    {
        static::getInstance()->default()->info($message, $context);
    }

    /**
     * Log an error message.
     *
     * Use this method to log runtime errors that do not require immediate
     * action but should be monitored and addressed. This includes exceptions,
     * failed operations, or other error conditions that don't crash the application.
     *
     * @param string $message The error message to log
     * @param array<mixed, mixed> $context Additional context data to include with the log entry
     * @return void
     */
    public static function error(string $message, array $context = []): void
    {
        static::getInstance()->default()->error($message, $context);
    }

    /**
     * Log a debug message.
     *
     * Use this method to log detailed diagnostic information that is typically
     * only of interest when diagnosing problems. Debug messages are usually
     * filtered out in production environments.
     *
     * @param string $message The debug message to log
     * @param array<mixed, mixed> $context Additional context data to include with the log entry
     * @return void
     */
    public static function debug(string $message, array $context = []): void
    {
        static::getInstance()->default()->debug($message, $context);
    }

    /**
     * Log a warning message.
     *
     * Use this method to log exceptional occurrences that are not errors
     * but may indicate potential issues. Warnings represent situations
     * that should be noted but don't prevent normal operation.
     *
     * @param string $message The warning message to log
     * @param array<mixed, mixed> $context Additional context data to include with the log entry
     * @return void
     */
    public static function warning(string $message, array $context = []): void
    {
        static::getInstance()->default()->warning($message, $context);
    }

    /**
     * Log a notice message.
     *
     * Use this method to log normal but significant events that are
     * worth noting. Notices are more important than info messages
     * but less severe than warnings.
     *
     * @param string $message The notice message to log
     * @param array<mixed, mixed> $context Additional context data to include with the log entry
     * @return void
     */
    public static function notice(string $message, array $context = []): void
    {
        static::getInstance()->default()->notice($message, $context);
    }

    /**
     * Log a critical message.
     *
     * Use this method to log critical conditions that require immediate
     * attention, such as application component unavailability or system
     * failures that may impact functionality but don't make the system unusable.
     *
     * @param string $message The critical message to log
     * @param array<mixed, mixed> $context Additional context data to include with the log entry
     * @return void
     */
    public static function critical(string $message, array $context = []): void
    {
        static::getInstance()->default()->critical($message, $context);
    }

    /**
     * Log an alert message.
     *
     * Use this method to log situations where action must be taken immediately.
     * Alerts represent urgent conditions that require prompt intervention
     * to prevent system degradation or failure.
     *
     * @param string $message The alert message to log
     * @param array<mixed, mixed> $context Additional context data to include with the log entry
     * @return void
     */
    public static function alert(string $message, array $context = []): void
    {
        static::getInstance()->default()->alert($message, $context);
    }

    /**
     * Log an emergency message.
     *
     * Use this method to log the most severe conditions where the system
     * is unusable or about to crash. Emergency messages indicate situations
     * that require immediate attention to restore system functionality.
     *
     * @param string $message The emergency message to log
     * @param array<mixed, mixed> $context Additional context data to include with the log entry
     * @return void
     */
    public static function emergency(string $message, array $context = []): void
    {
        static::getInstance()->default()->emergency($message, $context);
    }
}
