<?php

declare(strict_types=1);

namespace Rogue\Mantle\Contracts;

/**
 * Interface for logger processors that format or modify log records.
 *
 * Logger processors are responsible for processing log records before they are
 * sent to handlers. They can modify the log record data, add additional context,
 * or format the message content.
 *
 */
interface LoggerProcessorInterface
{
    /**
     * Get the processor instance or callable.
     *
     * Returns a processor that can be used to process log records. The processor
     * can be either an object with an __invoke method or a callable function.
     *
     * @return object|callable The processor instance or callable that will process log records
     */
    public function getProcessor(): object|callable;
}
