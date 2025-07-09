<?php

declare(strict_types=1);

namespace Mantle\Loggers\Monolog\Processors;

use Monolog\Processor\IntrospectionProcessor as MonologIntrospectionProcessor;
use Monolog\Processor\ProcessorInterface as MonologProcessorInterface;
use Psr\Log\LogLevel;

class IntrospectionProcessor implements ProcessorInterface
{
    private MonologIntrospectionProcessor $processor;

    /**
     * @param 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning' $level
     * @param string[] $skipClassesPartials
     */
    public function __construct(
        string $level = LogLevel::DEBUG,
        array $skipClassesPartials = [],
        int $skipStackFramesCount = 0
    ) {
        $this->processor = new MonologIntrospectionProcessor(
            $level,
            $skipClassesPartials,
            $skipStackFramesCount
        );
    }

    public function getProcessor(): MonologProcessorInterface|callable
    {
        return $this->processor;
    }
}
