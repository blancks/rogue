<?php

declare(strict_types=1);

namespace Mantle\Contracts;

use Psr\Http\Server\RequestHandlerInterface;

interface MiddlewareDispatcherInterface extends RequestHandlerInterface
{
    /**
     * Set the middleware stack
     * @param string[] $middleware List of middleware classnames extending the PSR-15 MiddlewareInterface
     * @throws \ValueError If an invalid middleware is detected.
     */
    public function setMiddlewareStack(array $middleware): void;

    /**
     * Set the final request handler
     * @param RequestHandlerInterface $finalHandler
     */
    public function setFinalHandler(RequestHandlerInterface $finalHandler): void;
}
