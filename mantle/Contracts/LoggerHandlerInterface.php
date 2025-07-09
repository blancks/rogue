<?php

declare(strict_types=1);

namespace Rogue\Mantle\Contracts;

/**
 * Interface for logger handlers that provide access to the underlying handler object.
 *
 * This interface defines the contract for classes that wrap or contain
 * logger handlers and need to expose the underlying handler instance.
 */
interface LoggerHandlerInterface
{
    /**
     * Get the underlying handler object.
     *
     * @return object The handler object used for logging operations
     */
    public function getHandler(): object;
}
