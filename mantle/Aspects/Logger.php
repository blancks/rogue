<?php

declare(strict_types=1);

namespace Rogue\Mantle\Aspects;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Rogue\Mantle\Contracts\Traits\SingletonTrait;
use ValueError;

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

    private static int $minLogLevelSeverity = 0;

    /** @var array<string, int> */
    private static array $logLevelsSeverityMap = [
        LogLevel::DEBUG => 1,
        LogLevel::INFO => 2,
        LogLevel::NOTICE => 3,
        LogLevel::WARNING => 4,
        LogLevel::ERROR => 5,
        LogLevel::CRITICAL => 6,
        LogLevel::ALERT => 7,
        LogLevel::EMERGENCY => 8,
    ];

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
     * @param string $logLevel The minimum log level to allow.
     * @return void
     */
    public static function setLevel(string $logLevel): void
    {
        if (!isset(static::$logLevelsSeverityMap[$logLevel])) {
            throw new ValueError(sprintf('Unknown level "%s"', $logLevel));
        }

        static::$minLogLevelSeverity = static::$logLevelsSeverityMap[$logLevel];
    }

    /**
     * Proxy to LoggerInterface::log
     * @param string $level
     * @param string $message
     * @param array<mixed, mixed> $context
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        if (!static::isLevelAllowed($level)) {
            return;
        }

        static::getInstance()->log($level, $message, $context);
    }

    /**
     * Alias of Logger::log with "info" severity
     * @param string $message
     * @param array<mixed, mixed> $context
     */
    public static function info(string $message, array $context = []): void
    {
        static::log(LogLevel::INFO, $message, $context);
    }

    /**
     * Alias of Logger::log with "error" severity
     * @param string $message
     * @param array<mixed, mixed> $context
     */
    public static function error(string $message, array $context = []): void
    {
        static::log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Alias of Logger::log with "debug" severity
     * @param string $message
     * @param array<mixed, mixed> $context
     */
    public static function debug(string $message, array $context = []): void
    {
        static::log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Alias of Logger::log with "warning" severity
     * @param string $message
     * @param array<mixed, mixed> $context
     */
    public static function warning(string $message, array $context = []): void
    {
        static::log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Alias of Logger::log with "notice" severity
     * @param string $message
     * @param array<mixed, mixed> $context
     */
    public static function notice(string $message, array $context = []): void
    {
        static::log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Alias of Logger::log with "critical" severity
     * @param string $message
     * @param array<mixed, mixed> $context
     */
    public static function critical(string $message, array $context = []): void
    {
        static::log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Alias of Logger::log with "alert" severity
     * @param string $message
     * @param array<mixed, mixed> $context
     */
    public static function alert(string $message, array $context = []): void
    {
        static::log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Alias of Logger::log with "emergency" severity
     * @param string $message
     * @param array<mixed, mixed> $context
     */
    public static function emergency(string $message, array $context = []): void
    {
        static::log(LogLevel::EMERGENCY, $message, $context);
    }

    private static function isLevelAllowed(string $logLevel): bool
    {
        return static::$logLevelsSeverityMap[$logLevel] >= static::$minLogLevelSeverity;
    }
}
