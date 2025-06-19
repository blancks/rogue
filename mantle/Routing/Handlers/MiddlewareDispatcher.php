<?php

declare(strict_types=1);

namespace Rogue\Mantle\Routing\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ValueError;

/**
 * Dispatches a stack of middleware and delegates to the final handler.
 */
class MiddlewareDispatcher implements RequestHandlerInterface
{
    /** @var MiddlewareInterface[] $middlewareStack The stack of middleware to process. */
    private array $middlewareStack;

    /** @var RequestHandlerInterface $finalHandler The final request handler. */
    private RequestHandlerInterface $finalHandler;

    /**
     * @param object[] $middlewareStack The stack of middleware to process.
     * @param RequestHandlerInterface $finalHandler The final request handler.
     * @throws ValueError If an invalid middleware is found.
     */
    public function __construct(array $middlewareStack, RequestHandlerInterface $finalHandler)
    {
        $this->assertValidMiddlewares($middlewareStack);

        /** @var MiddlewareInterface[] $middlewareStack */
        $this->middlewareStack = $middlewareStack;
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
     * @param object[] $middlewareStack The stack of middleware to process.
     * @throws ValueError If an invalid middleware is found.
     */
    private function assertValidMiddlewares(array $middlewareStack): void
    {
        foreach ($middlewareStack as $middleware) {
            if ($middleware instanceof MiddlewareInterface) {
                continue;
            }

            throw new ValueError(sprintf('Invalid middleware: %s', get_class($middleware)));
        }
    }
}
