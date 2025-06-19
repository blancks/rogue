<?php

declare(strict_types=1);

namespace Rogue\Mantle\Contracts;

/**
 * Interface EventDispatcherInterface
 *
 * Defines the contract for an event dispatcher, which allows registering
 * listeners for events and dispatching events to those listeners.
 */
interface EventDispatcherInterface
{
    /**
     * Register a listener callback for a given event.
     *
     * @param string $event The event name.
     * @param callable $callback The listener callback to be invoked when the event is dispatched.
     * @return void
     */
    public function listen(string $event, callable $callback): void;

    /**
     * Dispatch an event to all registered listeners.
     *
     * @param string $event The event name.
     * @param mixed[] $payload The payload to pass to the listeners.
     * @return void
     */
    public function dispatch(string $event, array $payload = []): void;
}
