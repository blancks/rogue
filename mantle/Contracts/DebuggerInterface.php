<?php

declare(strict_types=1);

namespace Mantle\Contracts;

/**
 * Interface DebuggerInterface
 *
 * Defines the contract for debugger implementations in the application.
 * Debuggers are responsible for handling error reporting, exception catching,
 * and providing debugging tools to assist development and troubleshooting.
 */
interface DebuggerInterface
{
    /**
     * Registers the debugger handlers within the application.
     *
     * This method should be called early in the application lifecycle to set up
     * all necessary error handlers, exception catchers, and debugging tools.
     * Implementations should configure appropriate error reporting levels,
     * register shutdown functions if needed, and initialize any logging or
     * visualization components required for debugging.
     */
    public function init(): void;
}
