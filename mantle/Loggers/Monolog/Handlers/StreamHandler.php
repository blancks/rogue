<?php

declare(strict_types=1);

namespace Rogue\Mantle\Loggers\Monolog\Handlers;

use Monolog\Handler\HandlerInterface as MonologHandlerInterface;
use Monolog\Handler\StreamHandler as MonologStreamHandler;
use Psr\Log\LogLevel;

final readonly class StreamHandler implements HandlerInterface
{
    private MonologStreamHandler $handler;

    /**
     * @param resource|string $stream
     * @param 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning' $level
     * @param bool $bubble
     * @param null|int $filePermission
     * @param bool $useLocking
     * @param string $fileOpenMode
     */
    public function __construct(
        mixed $stream = 'php://stderr',
        string $level = LogLevel::DEBUG,
        bool $bubble = true,
        ?int $filePermission = null,
        bool $useLocking = false,
        string $fileOpenMode = 'a'
    ) {
        $this->handler = new MonologStreamHandler(
            $stream,
            $level,
            $bubble,
            $filePermission,
            $useLocking,
            $fileOpenMode
        );
    }

    public function getHandler(): MonologHandlerInterface
    {
        return $this->handler;
    }
}
