<?php

declare(strict_types=1);

namespace Rogue\Mantle\Events;

use Rogue\Mantle\Contracts\EventDispatcherInterface;

/**
 * Class EventDispatcher
 *
 * A simple event dispatcher that allows registering listeners for events
 * and dispatching events to those listeners.
 */
final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array<string, callable[]> List of event listeners.
     */
    protected $listeners = [];

    /**
     * Register a listener callback for a given event.
     *
     * @param string $event The event name.
     * @param callable $callback The listener callback to be invoked when the event is dispatched.
     * @return void
     */
    public function listen(string $event, callable $callback): void
    {
        $this->listeners[$event][] = $callback;
    }

    /**
     * Dispatch an event to all registered listeners.
     *
     * @param string $event The event name.
     * @param mixed[] $payload The payload to pass to the listeners.
     * @return void
     */
    public function dispatch(string $event, array $payload = []): void
    {
        foreach ($this->listeners[$event] ?? [] as $listener) {
            call_user_func($listener, ...array_merge($payload, [$event]));
        }
    }
}
