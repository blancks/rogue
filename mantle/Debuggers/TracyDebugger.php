<?php

declare(strict_types=1);

namespace Rogue\Mantle\Debuggers;

use LogicException;
use Rogue\Mantle\Contracts\DebuggerInterface;

/**
 * Tracy Debugger integration.
 *
 * This class wraps the Tracy debugging library for use within the Rogue framework.
 * Tracy provides powerful debugging tools including error handling and visualization.
 */
final class TracyDebugger implements DebuggerInterface
{
    /**
     * Constructor for TracyDebugger.
     * @throws LogicException If Tracy\Debugger is not available.
     */
    public function __construct()
    {
        if (!class_exists('\Tracy\Debugger')) {
            throw new LogicException('Tracy\\Debugger is not available');
        }
    }

    /**
     * Initialize the Tracy debugger.
     *
     * Enables the Tracy debugging functionality but disables the debug bar
     * to prevent interference with the application output.
     *
     * @return void
     */
    public function init(): void
    {
        \Tracy\Debugger::enable();
        \Tracy\Debugger::$showBar = false;
    }
}
