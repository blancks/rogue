<?php

declare(strict_types=1);

namespace Mantle\Loggers\Monolog\Handlers;

use Monolog\Handler\HandlerInterface as MonologHandlerInterface;
use Mantle\Contracts\LoggerHandlerInterface;

/**
 * Interface for Monolog handlers within the framework.
 *
 * This interface extends the base LoggerHandlerInterface to provide
 * specific functionality for working with Monolog handlers, bridging
 * the gap between Mantle's logging contracts and Monolog's handler system.
 */
interface HandlerInterface extends LoggerHandlerInterface
{
    /**
     * Get the underlying Monolog handler instance.
     *
     * This method provides access to the wrapped Monolog handler,
     * allowing for direct interaction with Monolog's handler API
     * when needed.
     *
     * @return MonologHandlerInterface The Monolog handler instance
     */
    public function getHandler(): MonologHandlerInterface;
}
