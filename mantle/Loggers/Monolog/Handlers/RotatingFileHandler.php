<?php

declare(strict_types=1);

namespace Rogue\Mantle\Loggers\Monolog\Handlers;

use Monolog\Handler\HandlerInterface as MonologHandlerInterface;
use Monolog\Handler\RotatingFileHandler as MonologRotatingFileHandler;
use Psr\Log\LogLevel;

final readonly class RotatingFileHandler implements HandlerInterface
{
    private MonologRotatingFileHandler $handler;

    /**
     * @param 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning' $level
     */
    public function __construct(
        string $filename,
        int $maxFiles = 0,
        string $level = LogLevel::DEBUG,
        bool $bubble = true,
        ?int $filePermission = null,
        bool $useLocking = false,
        string $dateFormat = 'Y-m-d',
        string $filenameFormat  = '{filename}-{date}'
    ) {
        $this->handler = new MonologRotatingFileHandler(
            $filename,
            $maxFiles,
            $level,
            $bubble,
            $filePermission,
            $useLocking,
            $dateFormat,
            $filenameFormat
        );
    }

    public function getHandler(): MonologHandlerInterface
    {
        return $this->handler;
    }
}
