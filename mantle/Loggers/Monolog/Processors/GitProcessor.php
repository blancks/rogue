<?php

declare(strict_types=1);

namespace Rogue\Mantle\Loggers\Monolog\Processors;

use Monolog\Processor\GitProcessor as MonologGitProcessor;
use Monolog\Processor\ProcessorInterface as MonologProcessorInterface;
use Psr\Log\LogLevel;

class GitProcessor implements ProcessorInterface
{
    private MonologGitProcessor $processor;

    /**
     * @param 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning' $level
     */
    public function __construct(string $level = LogLevel::DEBUG)
    {
        $this->processor = new MonologGitProcessor($level);
    }

    public function getProcessor(): MonologProcessorInterface|callable
    {
        return $this->processor;
    }
}
