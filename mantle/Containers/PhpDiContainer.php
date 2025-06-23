<?php

declare(strict_types=1);

namespace Rogue\Mantle\Containers;

use DI\Container;
use DI\ContainerBuilder;
use Rogue\Mantle\Contracts\ContainerInterface;

use function DI\get;

// TODO: autocompile definitions for production environment

/**
 * PHP-DI Container implementation.
 *
 * This class wraps the PHP-DI Container implementation to provide
 * a standardized container interface for the Rogue framework.
 * It handles dependency injection, autowiring, and service binding.
 */
final class PhpDiContainer implements ContainerInterface
{
    /**
     * The underlying PHP-DI container instance.
     *
     * @var Container
     */
    private Container $container;

    /**
     * Creates a new PHP-DI Container instance with autowiring enabled.
     */
    public function __construct()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAttributes(false);
        $this->container = $containerBuilder->build();
    }

    /**
     * Retrieves an entry from the container by its identifier.
     *
     * @param string $id The identifier of the entry to retrieve.
     * @return mixed The entry.
     * @throws \DI\NotFoundException If the entry is not found.
     * @throws \DI\DependencyException If an error occurs during retrieval.
     */
    public function get(string $id): mixed
    {
        return $this->container->get($id);
    }

    /**
     * Checks if the container can provide an entry for the given identifier.
     *
     * @param string $id The identifier to check.
     * @return bool True if the container can provide an entry, false otherwise.
     */
    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    /**
     * Binds an abstract type to a concrete implementation.
     *
     * @param string $abstract The abstract class or interface name.
     * @param string $concrete The concrete class name.
     */
    public function bind(string $abstract, string $concrete): void
    {
        $this->container->set($abstract, get($concrete));
    }

    /**
     * Creates a new instance of the specified class.
     *
     * @param string $abstract The class name to instantiate.
     * @return mixed The instantiated object.
     * @throws \DI\DependencyException If an error occurs during instantiation.
     * @throws \DI\NotFoundException If a dependency is not found.
     */
    public function make(string $abstract): mixed
    {
        return $this->container->make($abstract);
    }

    /**
     * Calls the given function or method and injects its dependencies.
     *
     * @param array{0: string, 1: string}|\Closure $callable The function or method to call.
     * @param array<string, string> $parameters Additional parameters to pass to the callable.
     * @return mixed The result of the function call.
     */
    public function call(array|\Closure $callable, array $parameters = []): mixed
    {
        return $this->container->call($callable, $parameters);
    }
}
