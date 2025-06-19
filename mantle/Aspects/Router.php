<?php

declare(strict_types=1);

namespace Rogue\Mantle\Aspects;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Rogue\Mantle\Contracts\RouterInterface;
use Rogue\Mantle\Contracts\Traits\SingletonTrait;

/**
 * Router
 *
 * Static facade for interacting with the application's routing system.
 * Provides methods to set, retrieve, and proxy route definitions and handling
 * via a singleton router instance.
 */
final class Router
{
    use SingletonTrait;

    private static RouterInterface $instance;

    /**
     * Set the router implementation to use.
     *
     * @param RouterInterface $router
     * @return void
     */
    public static function setInstance(RouterInterface $router): void
    {
        self::$instance = $router;
    }

    /**
     * Get the singleton router instance.
     */
    public static function getInstance(): RouterInterface
    {
        return self::$instance;
    }

    /**
     * Add a middleware to the global middleware stack.
     *
     * @param MiddlewareInterface $middleware The middleware to add.
     * @return void
     */
    public static function addMiddleware(MiddlewareInterface $middleware): void
    {
        static::getInstance()->addMiddleware($middleware);
    }

    /**
     * Proxy to RouterInterface::get
     * @param string $uri
     * @param string|string[] $action
     */
    public static function get(string $uri, array|string $action): void
    {
        static::getInstance()->get($uri, $action);
    }

    /**
     * Proxy to RouterInterface::post
     * @param string $uri
     * @param string|string[] $action
     */
    public static function post(string $uri, array|string $action): void
    {
        static::getInstance()->post($uri, $action);
    }

    /**
     * Proxy to RouterInterface::put
     * @param string $uri
     * @param string|string[] $action
     */
    public static function put(string $uri, array|string $action): void
    {
        static::getInstance()->put($uri, $action);
    }

    /**
     * Proxy to RouterInterface::patch
     * @param string $uri
     * @param string|string[] $action
     */
    public static function patch(string $uri, array|string $action): void
    {
        static::getInstance()->patch($uri, $action);
    }

    /**
     * Proxy to RouterInterface::delete
     * @param string $uri
     * @param string|string[] $action
     */
    public static function delete(string $uri, array|string $action): void
    {
        static::getInstance()->delete($uri, $action);
    }

    /**
     * Proxy to RouterInterface::options
     * @param string $uri
     * @param string|string[] $action
     */
    public static function options(string $uri, array|string $action): void
    {
        static::getInstance()->options($uri, $action);
    }

    /**
     * Proxy to RouterInterface::head
     * @param string $uri
     * @param string|string[] $action
     */
    public static function head(string $uri, array|string $action): void
    {
        static::getInstance()->head($uri, $action);
    }

    /**
     * Proxy to RouterInterface::routeDiscovery
     * @param string $rootNamespace
     * @param string $rootPath
     * @param array<string, string> $namespacePaths
     */
    public static function routeDiscovery(
        string $rootNamespace,
        string $rootPath,
        array $namespacePaths = []
    ): void {
        static::getInstance()->routeDiscovery($rootNamespace, $rootPath, $namespacePaths);
    }

    /**
     * Proxy to RouterInterface::handle
     * @param ServerRequestInterface $serverRequest
     * @return ResponseInterface
     */
    public static function handle(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return static::getInstance()->handle($serverRequest);
    }
}
