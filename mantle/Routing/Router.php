<?php

declare(strict_types=1);

namespace Rogue\Mantle\Routing;

use Closure;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Rogue\Mantle\Contracts\ContainerInterface;
use Rogue\Mantle\Contracts\EventDispatcherInterface;
use Rogue\Mantle\Contracts\RouteDiscoveryInterface;
use Rogue\Mantle\Contracts\RouterInterface;
use Rogue\Mantle\Http\Exceptions\NotFoundException;
use Rogue\Mantle\Http\HttpMethod;
use Rogue\Mantle\Routing\Handlers\MiddlewareDispatcherFactoryInterface;

/**
 * Class Router
 *
 * Handles HTTP route registration and dispatching to controllers or callables.
 */
final class Router implements RouterInterface
{
    /**
     * @var array<string, array<array{
     *  uripattern: string,
     *  pattern: string,
     *  action:string|string[]
     * }>> Stores routes grouped by HTTP verb.
     */
    private array $routes = [];

    /**
     * @var array<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * Router constructor.
     *
     * @param ContainerInterface $container The DI container instance.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        private ContainerInterface $container,
        private EventDispatcherInterface $eventDispatcher,
        private RouteDiscoveryInterface $routeDiscovery,
        private MiddlewareDispatcherFactoryInterface $midlewareDispatcherFactory
    ) {
    }

    /**
     * Add a middleware to the global middleware stack.
     *
     * @param MiddlewareInterface $middleware The middleware to add.
     * @return void
     */
    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Register a GET route.
     *
     * @param string $uri
     * @param string|string[] $action
     */
    public function get(string $uri, array|string $action): void
    {
        $this->addRoute(HttpMethod::GET, $uri, $action);
    }

    /**
     * Register a POST route.
     *
     * @param string $uri
     * @param string|string[] $action
     */
    public function post(string $uri, array|string $action): void
    {
        $this->addRoute(HttpMethod::POST, $uri, $action);
    }

    /**
     * Register a PUT route.
     *
     * @param string $uri
     * @param string|string[] $action
     */
    public function put(string $uri, array|string $action): void
    {
        $this->addRoute(HttpMethod::PUT, $uri, $action);
    }

    /**
     * Register a PATCH route.
     *
     * @param string $uri
     * @param string|string[] $action
     */
    public function patch(string $uri, array|string $action): void
    {
        $this->addRoute(HttpMethod::PATCH, $uri, $action);
    }

    /**
     * Register a DELETE route.
     *
     * @param string $uri
     * @param string|string[] $action
     */
    public function delete(string $uri, array|string $action): void
    {
        $this->addRoute(HttpMethod::DELETE, $uri, $action);
    }

    /**
     * Register an OPTIONS route.
     *
     * @param string $uri
     * @param string|string[] $action
     */
    public function options(string $uri, array|string $action): void
    {
        $this->addRoute(HttpMethod::OPTIONS, $uri, $action);
    }

    /**
     * Register a HEAD route.
     *
     * @param string $uri
     * @param string|string[] $action
     */
    public function head(string $uri, array|string $action): void
    {
        $this->addRoute(HttpMethod::HEAD, $uri, $action);
    }

    /**
     * Dispatch the request.
     *
     * @param ServerRequestInterface $serverRequest
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->dispatch($serverRequest);
    }

    /**
     * @param string $rootNamespace
     * @param string $rootPath
     * @param array<string, string> $namespacePaths
     */
    public function routeDiscovery(
        string $rootNamespace,
        string $rootPath,
        array $namespacePaths = []
    ): void {
        $routes = $this->routeDiscovery->discover($rootNamespace, $rootPath, $namespacePaths);

        foreach ($routes as $route) {
            $this->addRoute($route['method'], $route['path'], $route['action']);
        }
    }

    /**
     * Add a route for a specific HTTP verb.
     *
     * @param HttpMethod $httpMethod
     * @param string $uri
     * @param string|string[] $action
     */
    private function addRoute(HttpMethod $httpMethod, string $uri, array|string $action): void
    {
        $verb = $httpMethod->value;
        $pattern = preg_replace('#\{([\w]+)\}#', '(?P<$1>[^/]+)', $uri);

        $this->routes[$verb][] = [
            'uripattern' => $uri,
            'pattern' => "#^$pattern\$#",
            'action' => $action,
        ];

        $this->eventDispatcher->dispatch('router.route.added', [$verb, $uri]);
    }

    /**
     * Dispatch the request to the appropriate route handler.
     *
     * @param ServerRequestInterface $serverRequest
     * @return ResponseInterface
     */
    private function dispatch(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $method = $serverRequest->getMethod();
        $uri = (string)$serverRequest->getUri()->getPath();

        $this->eventDispatcher->dispatch('router.dispatch.begin', [$method, $uri]);
        $dispatchClosure = $this->getResolveFunction($method, $uri);

        $response = $this->midlewareDispatcherFactory
            ->create($this->middlewares, $dispatchClosure)
            ->handle($serverRequest);

        $this->eventDispatcher->dispatch('router.dispatch.served', [
            $method,
            $uri,
            $response->getStatusCode()
        ]);

        return $response;
    }

    /**
     * Returns a function that invokes the routed controller or action.
     *
     * @param string $method The HTTP method (e.g., GET, POST).
     * @param string $uri The request URI to match against registered routes.
     * @return Closure The closure to handle the route.
     * @throws NotFoundException
     */
    private function getResolveFunction(string $method, string $uri): Closure
    {
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            if (is_string($route['action'])) {
                $controller = $this->container->make($route['action']);

                if (is_callable($controller)) {
                    return fn () => $controller();
                }

                return fn () => throw new LogicException(
                    sprintf(
                        'Action "%s" is not an invokable class for route: %s %s',
                        $route['action'],
                        $method,
                        $route['uripattern']
                    )
                );
            }

            [$classname, $method] = $route['action'];
            $params = array_filter($matches, fn ($key) => !is_int($key), ARRAY_FILTER_USE_KEY);

            return function () use ($classname, $method, $params) {
                $controller = $this->container->make($classname);
                return $this->container->call([$controller, $method], $params);
            };
        }

        return fn () => throw new NotFoundException();
    }
}
