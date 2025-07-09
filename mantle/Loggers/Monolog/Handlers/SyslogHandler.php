<?php

declare(strict_types=1);

namespace Rogue\Mantle\Loggers\Monolog\Handlers;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface as MonologHandlerInterface;
use Monolog\Handler\SyslogHandler as MonologSyslogHandler;
use Psr\Log\LogLevel;

final readonly class SyslogHandler implements HandlerInterface
{
    private MonologSyslogHandler $handler;

    /**
     * @param 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning' $level
     */
    public function __construct(
        string $ident,
        string|int $facility = LOG_USER,
        string $level = LogLevel::DEBUG,
        bool $bubble = true,
        int $logopts = LOG_PID
    ) {
        $this->handler = new MonologSyslogHandler(
            $ident,
            $facility,
            $level,
            $bubble,
            $logopts
        );

        $this->handler->setFormatter(
            new LineFormatter(
                '%channel%.%level_name%: %message% %extra%'
            )
        );
    }

    public function getHandler(): MonologHandlerInterface
    {
        return $this->handler;
    }
}
