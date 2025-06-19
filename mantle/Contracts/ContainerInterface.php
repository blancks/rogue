<?php

declare(strict_types=1);

namespace Rogue\Mantle\Contracts;

/**
 * Interface ContainerInterface
 *
 * Defines the contract for a dependency injection container.
 */
interface ContainerInterface
{
    /**
     * Bind an abstract type to a concrete implementation or factory.
     *
     * @param string $abstract
     * @param string $concrete
     * @return void
     */
    public function bind(string $abstract, string $concrete): void;

    /**
     * Resolve an instance of the given abstract type.
     *
     * @param string|object $abstract
     * @return object
     */
    public function make(string|object $abstract): object;

    /**
     * Call the given callable, automatically injecting dependencies.
     *
     * @param array{0: object|string, 1: string}|\Closure $callable
     * @param array<string, mixed> $parameters
     * @return mixed
     */
    public function call(array|\Closure $callable, array $parameters = []): mixed;
}
