<?php

declare(strict_types=1);

namespace Mantle\Aspects;

use Mantle\Contracts\ContainerInterface;
use Mantle\Contracts\Traits\SingletonTrait;
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
     * Retrieves an entry from the container by its identifier.
     *
     * @param string $id The identifier of the entry to retrieve.
     * @return mixed The entry.
     * @throws \Psr\Container\NotFoundExceptionInterface If the entry is not found.
     * @throws \Psr\Container\ContainerExceptionInterface If an error occurs during retrieval.
     */
    public static function get(string $id): mixed
    {
        return static::getInstance()->get($id);
    }

    /**
     * Checks if the container can provide an entry for the given identifier.
     *
     * @param string $id The identifier to check.
     * @return bool True if the container can provide an entry, false otherwise.
     */
    public static function has(string $id): bool
    {
        return static::getInstance()->has($id);
    }

    /**
     * Resolve an instance of the given abstract type.
     */
    public static function make(string $abstract): mixed
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
