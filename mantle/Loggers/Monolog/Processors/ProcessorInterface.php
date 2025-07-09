<?php

declare(strict_types=1);

namespace Rogue\Mantle\Loggers\Monolog\Processors;

use Monolog\Processor\ProcessorInterface as MonologProcessorInterface;
use Rogue\Mantle\Contracts\LoggerProcessorInterface;

/**
 * Interface for Monolog processors in the framework.
 *
 * This interface extends the base LoggerProcessorInterface and provides
 * a contract for processor implementations that work with Monolog's
 * processor system.
 */
interface ProcessorInterface extends LoggerProcessorInterface
{
    /**
     * Get the underlying Monolog processor instance or callable.
     *
     * @return MonologProcessorInterface|callable The processor instance or callable
     *                                           that can be used to process log records
     */
    public function getProcessor(): MonologProcessorInterface|callable;
}
