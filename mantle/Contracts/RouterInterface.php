<?php

declare(strict_types=1);

namespace Mantle\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Mantle\Http\HttpMethod;

/**
 * Interface RouterInterface
 *
 * Defines the contract for HTTP routing.
 */
interface RouterInterface
{
    /**
     * Add a middleware to the global middleware stack.
     *
     * @param string $middleware FQCN of the middleware. Must implement \Psr\Http\Server\MiddlewareInterface
     * @return void
     */
    public function addMiddleware(string $middleware): void;

    /**
     * Register a GET route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     * @return void
     */
    public function get(string $uri, array|string $action, array $middleware = []): void;

    /**
     * Register a POST route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     * @return void
     */
    public function post(string $uri, array|string $action, array $middleware = []): void;

    /**
     * Register a PUT route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     * @return void
     */
    public function put(string $uri, array|string $action, array $middleware = []): void;

    /**
     * Register a PATCH route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     * @return void
     */
    public function patch(string $uri, array|string $action, array $middleware = []): void;

    /**
     * Register a DELETE route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     * @return void
     */
    public function delete(string $uri, array|string $action, array $middleware = []): void;

    /**
     * Register an OPTIONS route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     * @return void
     */
    public function options(string $uri, array|string $action, array $middleware = []): void;

    /**
     * Register a HEAD route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     * @return void
     */
    public function head(string $uri, array|string $action, array $middleware = []): void;

    /**
     * Dispatch the request.
     *
     * @param ServerRequestInterface $serverRequest
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $serverRequest): ResponseInterface;

    /**
     * Add a route for a specific HTTP verb.
     *
     * @param HttpMethod $httpMethod
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     */
    public function addRoute(
        HttpMethod $httpMethod,
        string $uri,
        array|string $action,
        array $middleware = []
    ): void;

    /**
     * Discover and register routes from given namespaces and paths.
     *
     * @param string $rootNamespace
     * @param string $rootPath
     * @param array<string, string> $namespacePaths Array of additional namespace => path pairs to scan
     * @return void
     */
    public function routeDiscovery(
        string $rootNamespace,
        string $rootPath,
        array $namespacePaths = []
    ): void;
}
