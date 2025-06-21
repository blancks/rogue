<?php

declare(strict_types=1);

namespace Rogue\Mantle\Routing\Handlers;

use Closure;
use Psr\Http\Server\RequestHandlerInterface;
use Rogue\Mantle\Aspects\Response;
use Rogue\Mantle\Contracts\ContainerAwareInterface;
use Rogue\Mantle\Contracts\ContainerInterface;
use Rogue\Mantle\Contracts\MiddlewareDispatcherInterface;

/**
 * Implements a factory for creating middleware dispatcher instances.
 */
class MiddlewareDispatcherFactory implements MiddlewareDispatcherFactoryInterface
{
    /**
     * @param string $className The fully qualified class name of the dispatcher to instantiate.
     * @param ContainerInterface $container The DI container instance.
     */
    public function __construct(
        private string $className,
        private ContainerInterface $container
    ) {
    }

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
    ): RequestHandlerInterface {
        $requestHandler = new ($this->className);

        if (!($requestHandler instanceof MiddlewareDispatcherInterface)) {
            throw new \LogicException(
                'Invalid middleware dispatcher class. It must implement MiddlewareDispatcherInterface'
            );
        }

        if ($requestHandler instanceof ContainerAwareInterface) {
            $requestHandler->setContainer($this->container);
        }

        $requestHandler->setFinalHandler(new FinalRequestHandler($dispatchClosure, Response::getInstance()));
        $requestHandler->setMiddlewareStack($middlewareStack);

        return $requestHandler;
    }
}
