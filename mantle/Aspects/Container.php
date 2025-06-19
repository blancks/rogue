<?php

declare(strict_types=1);

namespace Rogue\Mantle\Aspects;

use Rogue\Mantle\Contracts\ContainerInterface;
use Rogue\Mantle\Contracts\Traits\SingletonTrait;
use ValueError;

/**
 * Container
 *
 * Static facade for interacting with the application's dependency injection container.
 * Provides methods to set, retrieve, bind, and resolve dependencies via a singleton container instance.
 */
final class Container
{
    use SingletonTrait;

    private static ContainerInterface $instance;

    /**
     * Set the container implementation to use.
     *
     * @param ContainerInterface $container
     * @return void
     */
    public static function setInstance(ContainerInterface $container): void
    {
        self::$instance = $container;
    }

    /**
     * Get the singleton container instance.
     */
    public static function getInstance(): ContainerInterface
    {
        return self::$instance;
    }

    /**
     * Bind an abstract type to a concrete implementation.
     * @param string|array<string, string> $abstract
     * @param ?string $concrete
     */
    public static function bind(string|array $abstract, ?string $concrete = null): void
    {
        if (is_array($abstract)) {
            $container = self::getInstance();

            foreach ($abstract as $interface => $className) {
                $container->bind($interface, $className);
            }

            return;
        }

        if (is_null($concrete)) {
            throw new ValueError('concrete param cannot be null');
        }

        static::getInstance()->bind($abstract, $concrete);
    }

    /**
     * Resolve an instance of the given abstract type.
     */
    public static function make(string|object $abstract): object
    {
        return static::getInstance()->make($abstract);
    }

    /**
     * Call the given callable, automatically injecting dependencies.
     * @param array{0: object|string, 1: string}|\Closure $callable
     * @param array<string, mixed> $parameters
     */
    public static function call(array|\Closure $callable, array $parameters = []): mixed
    {
        return static::getInstance()->call($callable, $parameters);
    }
}
