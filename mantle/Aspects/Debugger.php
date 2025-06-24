<?php

declare(strict_types=1);

namespace Rogue\Mantle\Aspects;

use Rogue\Mantle\Contracts\DebuggerInterface;
use Rogue\Mantle\Contracts\Traits\SingletonTrait;

/**
 * The Debugger class provides a static facade for accessing the debugger implementation.
 *
 * This class serves as a bridge between application code and the debugger implementation,
 * allowing for a standardized way of accessing debugging functionality throughout the application.
 * It implements the singleton pattern to ensure a single instance is used across the application.
 */
final class Debugger
{
    use SingletonTrait;

    private static DebuggerInterface $instance;

    /**
     * Set the debugger implementation to use.
     *
     * @param DebuggerInterface $debugger
     * @return void
     */
    public static function setInstance(DebuggerInterface $debugger): void
    {
        self::$instance = $debugger;
    }

    /**
     * Get the debugger instance.
     *
     * @return DebuggerInterface
     */
    public static function getInstance(): DebuggerInterface
    {
        return self::$instance;
    }

    /**
     * Initialize the debugger.
     *
     * This method initializes the underlying debugger implementation by calling its init method.
     * Should be called during the application bootstrap process after setting an instance.
     *
     * @return void
     */
    public static function init(): void
    {
        self::$instance->init();
    }
}
