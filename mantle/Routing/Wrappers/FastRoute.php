<?php

declare(strict_types=1);

namespace Rogue\Mantle\Routing\Wrappers;

use Closure;
use FastRoute\Dispatcher;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rogue\Mantle\Contracts\ContainerInterface;
use Rogue\Mantle\Contracts\EventDispatcherInterface;
use Rogue\Mantle\Contracts\RouteDiscoveryInterface;
use Rogue\Mantle\Contracts\RouterInterface;
use Rogue\Mantle\Http\Exceptions\MethodNotAllowedException;
use Rogue\Mantle\Http\Exceptions\NotFoundException;
use Rogue\Mantle\Http\HttpMethod;
use Rogue\Mantle\Routing\Handlers\MiddlewareDispatcherFactoryInterface;

/**
 * Class FastRoute
 *
 * Handles HTTP route registration and dispatching to controllers or callables.
 */
final class FastRoute implements RouterInterface
{
    /**
     * @var array<string, array<array{
     *  pattern: string,
     *  action:string|string[],
     *  middleware: string[]
     * }>> Stores routes grouped by HTTP verb.
     */
    private array $routes = [];

    /** @var string[] List of middleware classnames */
    private array $middlewares = [];

    /** @var Dispatcher FastRoute dispatcher instance */
    private Dispatcher $dispatcher;

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
     * Add a global middleware to the middleware stack.
     *
     * @param string $middleware FQCN of the middleware. Must implement \Psr\Http\Server\MiddlewareInterface
     * @return void
     */
    public function addMiddleware(string $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Register a GET route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     */
    public function get(string $uri, array|string $action, array $middleware = []): void
    {
        $this->addRoute(HttpMethod::GET, $uri, $action, $middleware);
    }

    /**
     * Register a POST route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     */
    public function post(string $uri, array|string $action, array $middleware = []): void
    {
        $this->addRoute(HttpMethod::POST, $uri, $action, $middleware);
    }

    /**
     * Register a PUT route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     */
    public function put(string $uri, array|string $action, array $middleware = []): void
    {
        $this->addRoute(HttpMethod::PUT, $uri, $action, $middleware);
    }

    /**
     * Register a PATCH route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     */
    public function patch(string $uri, array|string $action, array $middleware = []): void
    {
        $this->addRoute(HttpMethod::PATCH, $uri, $action, $middleware);
    }

    /**
     * Register a DELETE route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     */
    public function delete(string $uri, array|string $action, array $middleware = []): void
    {
        $this->addRoute(HttpMethod::DELETE, $uri, $action, $middleware);
    }

    /**
     * Register an OPTIONS route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     */
    public function options(string $uri, array|string $action, array $middleware = []): void
    {
        $this->addRoute(HttpMethod::OPTIONS, $uri, $action, $middleware);
    }

    /**
     * Register a HEAD route.
     *
     * @param string $uri
     * @param string|string[] $action
     * @param string[] $middleware
     */
    public function head(string $uri, array|string $action, array $middleware = []): void
    {
        $this->addRoute(HttpMethod::HEAD, $uri, $action, $middleware);
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
        // TODO: implement cached routes for production
        $this->dispatcher = \FastRoute\simpleDispatcher(
            function (\FastRoute\RouteCollector $fastRouter) use (
                $rootNamespace,
                $rootPath,
                $namespacePaths
            ) {
                $this->performRouteDiscovery($rootNamespace, $rootPath, $namespacePaths);

                foreach ($this->routes as $method => $routes) {
                    foreach ($routes as $route) {
                        $fastRouter->addRoute(
                            httpMethod: $method,
                            route: $route['pattern'],
                            handler: [$route['action'], $route['middleware']]
                        );
                    }
                }
            }
        );
    }

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
    ): void {
        $verb = $httpMethod->value;

        $this->routes[$verb][] = [
            'pattern' => $uri,
            'action' => $action,
            'middleware' => $middleware,
        ];

        $this->eventDispatcher->dispatch('router.route.added', [$verb, $uri]);
    }

    /**
     * @param string $rootNamespace
     * @param string $rootPath
     * @param array<string, string> $namespacePaths
     */
    private function performRouteDiscovery(
        string $rootNamespace,
        string $rootPath,
        array $namespacePaths = []
    ): void {
        $routes = $this->routeDiscovery->discover($rootNamespace, $rootPath, $namespacePaths);

        foreach ($routes as $route) {
            $this->addRoute($route['method'], $route['path'], $route['action'], $route['middleware']);
        }
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

        $routeInfo = $this->dispatcher->dispatch($method, $uri);
        $dispatchClosure = $this->dispatcherResultToResolveFunction($routeInfo);

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
     * Side effect: if the matching route has middlewares they will be added to the stack.
     *
     * @param array<mixed> $routeInfo data returned by FastRoute dispatcher.
     * @return Closure The closure to handle the route.
     * @throws NotFoundException
     */
    private function dispatcherResultToResolveFunction(array $routeInfo): Closure
    {
        if (!isset($routeInfo[0])) {
            return fn () => throw new LogicException('nikic/fast-route did not returned a dispatcher status');
        }

        /** @var array{0: int} $routeInfo */
        $dispatcherStatus = $routeInfo[0];

        switch ($dispatcherStatus) {
            case Dispatcher::NOT_FOUND:
                return fn () => throw new NotFoundException();

            case Dispatcher::METHOD_NOT_ALLOWED:
                /** @var array{0: int, 1: string[]} $routeInfo */
                return fn () => throw new MethodNotAllowedException($routeInfo[1]);

            case Dispatcher::FOUND:
                /** @var array{0: int, 1: array{0: string|string[], 1: string[]}, 2: array<string,string>} $routeInfo */
                [$handler, $middleware] = $routeInfo[1];
                $params = $routeInfo[2];

                $this->middlewares = array_merge($this->middlewares, $middleware);

                if (is_string($handler)) {
                    $controller = $this->container->make($handler);

                    if (is_callable($controller)) {
                        return fn () => $controller(...$params);
                    }

                    return fn () => throw new LogicException(
                        sprintf('Invalid invokable class: %s', $handler)
                    );
                }

                [$className, $classMethod] = $handler;

                return function () use ($className, $classMethod, $params) {
                    $controller = $this->container->make($className);

                    if (!is_object($controller)) {
                        throw new LogicException(sprintf('Invalid controller class: %s', $className));
                    }

                    return $this->container->call([$controller, $classMethod], $params);
                };

            default:
                return fn () => throw new LogicException(
                    sprintf(
                        'nikic/fast-route returned an unknown dispatch status: %s',
                        (string)$dispatcherStatus
                    )
                );
        }
    }
}
