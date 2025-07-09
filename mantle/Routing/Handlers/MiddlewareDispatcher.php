<?php

declare(strict_types=1);

namespace Mantle\Routing\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mantle\Contracts\ContainerAwareInterface;
use Mantle\Contracts\MiddlewareDispatcherInterface;
use Mantle\Contracts\Traits\ContainerAwareTrait;
use ValueError;

/**
 * Dispatches a stack of middleware and delegates to the final handler.
 */
class MiddlewareDispatcher implements MiddlewareDispatcherInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var MiddlewareInterface[] $middlewareStack The stack of middleware to process. */
    private array $middlewareStack;

    /** @var RequestHandlerInterface $finalHandler The final request handler. */
    private RequestHandlerInterface $finalHandler;

    /**
     * Provides the middleware stack to the dispatcher
     * @param string[] $middleware List of middleware classnames extending the PSR-15 MiddlewareInterface
     * @throws ValueError If an invalid middleware is detected.
     */
    public function setMiddlewareStack(array $middleware): void
    {
        $this->middlewareStack = $this->getMiddlewareInstances($middleware);
    }

    /**
     * Set the final request handler
     * @param RequestHandlerInterface $finalHandler
     */
    public function setFinalHandler(RequestHandlerInterface $finalHandler): void
    {
        $this->finalHandler = $finalHandler;
    }

    /**
     * Handles the incoming server request by processing the middleware stack.
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @return ResponseInterface The response after middleware processing.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (count($this->middlewareStack) === 0) {
            return $this->finalHandler->handle($request);
        }

        $middleware = array_shift($this->middlewareStack);
        return $middleware->process($request, $this);
    }

    /**
     * Validates that all items in the middleware stack implement MiddlewareInterface.
     *
     * @param string[] $middlewareStack The stack of middleware to instantiate.
     * @return MiddlewareInterface[]
     * @throws ValueError If an invalid middleware is detected.
     */
    private function getMiddlewareInstances(array $middlewareStack): array
    {
        $instances = [];

        foreach ($middlewareStack as $middlewareClass) {
            $middleware = $this->container->make($middlewareClass);

            if ($middleware instanceof MiddlewareInterface) {
                $instances[] = $middleware;
                continue;
            }

            throw new ValueError(sprintf('Invalid middleware: %s', $middlewareClass));
        }

        return $instances;
    }
}
