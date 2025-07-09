<?php

declare(strict_types=1);

namespace Mantle\Aspects;

use Mantle\Contracts\EventDispatcherInterface;
use Mantle\Contracts\Traits\SingletonTrait;

/**
 * EventDispatcher
 *
 * Static facade for interacting with the application's event dispatcher.
 * Provides methods to set, retrieve, and proxy event listener registration and dispatching
 * via a singleton event dispatcher instance.
 */
final class EventDispatcher
{
    use SingletonTrait;

    private static EventDispatcherInterface $instance;

    /**
     * Set the event dispatcher implementation to use.
     *
     * @param EventDispatcherInterface $dispatcher
     * @return void
     */
    public static function setInstance(EventDispatcherInterface $dispatcher): void
    {
        self::$instance = $dispatcher;
    }

    /**
     * Get the singleton event dispatcher instance.
     */
    public static function getInstance(): EventDispatcherInterface
    {
        return self::$instance;
    }

    /**
     * Proxy to EventDispatcherInterface::listen
     */
    public static function listen(string $event, callable $callback): void
    {
        static::getInstance()->listen($event, $callback);
    }

    /**
     * Proxy to EventDispatcherInterface::dispatch
     * @param string $event
     * @param mixed[] $payload
     */
    public static function dispatch(string $event, array $payload = []): void
    {
        static::getInstance()->dispatch($event, $payload);
    }
}
