<?php

declare(strict_types=1);

namespace Mantle\Routing\Handlers;

use Closure;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Defines a factory for creating middleware dispatcher instances.
 */
interface MiddlewareDispatcherFactoryInterface
{
    /**
     * Create a new middleware dispatcher instance.
     *
     * @param string[] $middlewareStack The stack of middleware to dispatch.
     * @param Closure $dispatchClosure The dispatch closure that resolves the routed controller.
     * @return RequestHandlerInterface The composed middleware dispatcher.
     */
    public function create(
        array $middlewareStack,
        Closure $dispatchClosure
    ): RequestHandlerInterface;
}
