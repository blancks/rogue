<?php

declare(strict_types=1);

namespace Rogue\Mantle\Loggers\Monolog\Handlers;

use Monolog\Handler\HandlerInterface as MonologHandlerInterface;
use Monolog\Handler\RedisHandler as MonologRedisHandler;
use Psr\Log\LogLevel;

final readonly class RedisHandler implements HandlerInterface
{
    private MonologRedisHandler $handler;

    /**
     * @param \Redis|\Predis\Client $redis
     * @param string $key
     * @param 'debug'|'info'|'notice'|'warning'|'error'|'critical'|'alert'|'emergency' $level
     * @param bool $bubble
     * @param int $capSize
     */
    public function __construct(
        // @phpstan-ignore-next-line if predis is not installed phpstan marks this as an error
        \Redis|\Predis\Client $redis,
        string $key,
        string $level = LogLevel::DEBUG,
        bool $bubble = true,
        int $capSize = 0
    ) {
        $this->handler = new MonologRedisHandler(
            $redis,
            $key,
            $level,
            $bubble,
            $capSize
        );
    }

    public function getHandler(): MonologHandlerInterface
    {
        return $this->handler;
    }
}
