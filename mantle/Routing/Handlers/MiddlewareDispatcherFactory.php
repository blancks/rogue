<?php

declare(strict_types=1);

namespace Rogue\Mantle\Routing\Handlers;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rogue\Mantle\Aspects\Response;

/**
 * Implements a factory for creating middleware dispatcher instances.
 */
class MiddlewareDispatcherFactory implements MiddlewareDispatcherFactoryInterface
{
    /**
     * @param string $className The fully qualified class name of the dispatcher to instantiate.
     */
    public function __construct(private string $className)
    {
    }

    /**
     * Create a new middleware dispatcher instance.
     *
     * @param MiddlewareInterface[] $middlewareStack The stack of middleware to dispatch.
     * @param Closure $dispatchClosure The dispatch closure that resolves the routed controller.
     * @return RequestHandlerInterface The composed middleware dispatcher.
     */
    public function create(
        array $middlewareStack,
        Closure $dispatchClosure
    ): RequestHandlerInterface {
        /** @var RequestHandlerInterface $requestHandler */
        $requestHandler = new ($this->className)(
            $middlewareStack,
            new FinalRequestHandler($dispatchClosure, Response::getInstance()) // TODO: inject dependency
        );

        return $requestHandler;
    }
}
