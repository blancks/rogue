<?php

declare(strict_types=1);

namespace Rogue\Mantle\Containers;

use Rogue\Mantle\Contracts\ContainerInterface;

/**
 * Class DependencyInjectionContainer
 *
 * A simple dependency injection container for binding and resolving classes and callables.
 *
 * This container implements the Inversion of Control (IoC) principle by allowing you to bind
 * abstract types to concrete implementations or factories. Instead of classes instantiating
 * their own dependencies, the container resolves and injects them automatically, promoting
 * loose coupling and easier testing.
 */
final class DependencyInjectionContainer implements ContainerInterface
{
    /**
     * @var array<string, string> Stores bindings from abstract types to concrete implementations
     */
    protected $bindings = [];

    /**
     * Bind an abstract type to a concrete implementation or factory.
     *
     * @param string $abstract The abstract type (usually an interface or class name).
     * @param string $concrete The concrete implementation (class name).
     * @return void
     */
    public function bind(string $abstract, string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Resolve an instance of the given abstract type.
     *
     * @param string|object $abstract The abstract type to resolve.
     * @return object The resolved instance.
     * @throws \Exception If the class cannot be resolved.
     */
    public function make(string|object $abstract): object
    {
        if (is_string($abstract)) {
            $concrete = $this->bindings[$abstract] ?? $abstract;

            if (!class_exists($concrete)) {
                throw new \Exception("Class {$concrete} does not exist.");
            }
        } else {
            $concrete = $abstract;
        }

        $reflection = new \ReflectionClass($concrete);

        if (!$reflection->isInstantiable()) {
            throw new \Exception(
                sprintf(
                    'Class %s is not instantiable.',
                    is_string($concrete) ? $concrete : get_class($concrete)
                )
            );
        }

        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $concrete();
        }

        // Resolve constructor dependencies recursively.
        $dependencies = array_map(
            function (\ReflectionParameter $param) {
                $type = $param->getType();

                if (!($type instanceof \ReflectionNamedType)) {
                    throw new \Exception('Unresolvable dependency.');
                }

                return $this->make($type->getName());
            },
            $constructor->getParameters()
        );

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Call the given callable, automatically injecting dependencies.
     *
     * @param array{0: object|string, 1: string}|\Closure $callable The function or method to call.
     * @param array<string, mixed> $parameters Parameters to pass to the callable.
     * @return mixed The result of the callable.
     * @throws \Exception If a parameter cannot be resolved.
     */
    public function call(array|\Closure $callable, array $parameters = []): mixed
    {
        $reflection = is_array($callable)
            ? new \ReflectionMethod($callable[0], $callable[1])
            : new \ReflectionFunction($callable);

        $args = [];

        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $parameters)) {
                $args[] = $parameters[$name];
                continue;
            }

            $type = $param->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->make((string) $type->getName());
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            throw new \Exception("Unable to resolve parameter '{$name}'");
        }

        if (is_array($callable)) {
            return $reflection->invokeArgs(
                $this->make($callable[0]),
                $args
            );
        }

        return $reflection->invokeArgs($args);
    }
}
