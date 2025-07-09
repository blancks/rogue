<?php

declare(strict_types=1);

namespace Rogue\Mantle\Loggers\Monolog\Processors;

use Monolog\Processor\MemoryUsageProcessor as MonologMemoryUsageProcessor;
use Monolog\Processor\ProcessorInterface as MonologProcessorInterface;

class MemoryUsageProcessor implements ProcessorInterface
{
    private MonologMemoryUsageProcessor $processor;

    public function __construct()
    {
        $this->processor = new MonologMemoryUsageProcessor();
    }

    public function getProcessor(): MonologProcessorInterface|callable
    {
        return $this->processor;
    }
}
