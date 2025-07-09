<?php

declare(strict_types=1);

namespace Mantle\Loggers\Monolog\Processors;

use Monolog\Processor\ProcessIdProcessor as MonologProcessIdProcessor;
use Monolog\Processor\ProcessorInterface as MonologProcessorInterface;

class ProcessIdProcessor implements ProcessorInterface
{
    private MonologProcessIdProcessor $processor;

    public function __construct()
    {
        $this->processor = new MonologProcessIdProcessor();
    }

    public function getProcessor(): MonologProcessorInterface|callable
    {
        return $this->processor;
    }
}
