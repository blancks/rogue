<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Routing\Wrappers;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rogue\Mantle\Routing\Wrappers\FastRoute;
use Rogue\Mantle\Contracts\ContainerInterface;
use Rogue\Mantle\Contracts\EventDispatcherInterface;
use Rogue\Mantle\Contracts\RouteDiscoveryInterface;
use Rogue\Mantle\Routing\Handlers\MiddlewareDispatcherFactoryInterface;
use Rogue\Mantle\Http\HttpMethod;
use FastRoute\Dispatcher;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rogue\Mantle\Aspects\Response;
use Rogue\Mantle\Http\Exceptions\MethodNotAllowedException;
use Rogue\Mantle\Http\HttpStatus;

class FastRouteTest extends TestCase
{
    /** @var MockObject&ContainerInterface $container */
    private ContainerInterface $container;

    /** @var MockObject&EventDispatcherInterface $eventDispatcher */
    private EventDispatcherInterface $eventDispatcher;

    /** @var MockObject&RouteDiscoveryInterface $routeDiscovery */
    private RouteDiscoveryInterface $routeDiscovery;

    /** @var MockObject&MiddlewareDispatcherFactoryInterface $middlewareDispatcherFactory */
    private MiddlewareDispatcherFactoryInterface $middlewareDispatcherFactory;

    private FastRoute $fastRoute;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->routeDiscovery = $this->createMock(RouteDiscoveryInterface::class);
        $this->middlewareDispatcherFactory = $this->createMock(MiddlewareDispatcherFactoryInterface::class);
        $this->fastRoute = new FastRoute(
            $this->container,
            $this->eventDispatcher,
            $this->routeDiscovery,
            $this->middlewareDispatcherFactory
        );
    }

    public function testAddMiddleware(): void
    {
        // Arrange
        $reflection = new ReflectionClass($this->fastRoute);
        $property = $reflection->getProperty('middlewares');
        $property->setAccessible(true);

        // Act
        $this->fastRoute->addMiddleware('Some\\Middleware');

        // Assert
        $middlewares = $property->getValue($this->fastRoute);
        $this->assertContains('Some\\Middleware', $middlewares);
    }

    #[DataProvider('httpMethodProvider')]
    public function testRouteRegistration(HttpMethod $httpMethod): void
    {
        // Arrange
        $reflection = new ReflectionClass($this->fastRoute);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        $property->setValue($this->fastRoute, []);

        // Act
        $this->fastRoute->addRoute($httpMethod, '/foo', 'FooController@bar', ['Some\\Middleware']);

        // Assert
        $routes = $property->getValue($this->fastRoute);
        $this->assertArrayHasKey($httpMethod->value, $routes);
        $this->assertSame('/foo', $routes[$httpMethod->value][0]['pattern']);
        $this->assertSame('FooController@bar', $routes[$httpMethod->value][0]['action']);
        $this->assertSame(['Some\\Middleware'], $routes[$httpMethod->value][0]['middleware']);
    }

    #[DataProvider('httpMethodProvider')]
    public function testRouteRegistrationAliasMethod(HttpMethod $httpMethod): void
    {
        // Arrange
        $reflection = new ReflectionClass($this->fastRoute);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        $property->setValue($this->fastRoute, []);
        $classMethod = strtolower($httpMethod->value);

        // Act
        $this->fastRoute->{$classMethod}('/bar', 'BarController@foo', ['Foo\\Bar']);

        // Assert
        $routes = $property->getValue($this->fastRoute);
        $this->assertArrayHasKey($httpMethod->value, $routes);
        $this->assertSame('/bar', $routes[$httpMethod->value][0]['pattern']);
        $this->assertSame('BarController@foo', $routes[$httpMethod->value][0]['action']);
        $this->assertSame(['Foo\\Bar'], $routes[$httpMethod->value][0]['middleware']);
    }

    public function testDispatchFound(): void
    {
        // Arrange

        // Mocking the FastRoute dispatcher
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->method('dispatch')->willReturn([
            Dispatcher::FOUND,
            [['TestController', 'index'], []],
            ['id' => 1]
        ]);

        // Injecting the mocked FastRoute in the router class instance
        $reflection = new ReflectionClass($this->fastRoute);
        $property = $reflection->getProperty('dispatcher');
        $property->setAccessible(true);
        $property->setValue($this->fastRoute, $dispatcher);

        // Mocking the output response object
        $response = $this->createMock(ResponseInterface::class);

        // Configuring mocked container to fake the autowiring of a controller that returns $response
        $this->container->method('make')->willReturn(new class () {
            public function index($id = null)
            {
                return 'ok';
            }
        });
        $this->container->method('call')->willReturn($response);

        // Mocking the request handler
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->method('handle')->willReturn($response);
        $this->middlewareDispatcherFactory->method('create')->willReturn($requestHandler);

        // Incoming Server Request Mock
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($this->getMockedUri('/foo'));

        // Act
        $result = $this->fastRoute->handle($request);

        // Assert
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testDispatchNotFound(): void
    {
        // Arrange

        // Capture the closure passed to the middleware dispatcher factory
        $this->middlewareDispatcherFactory->method('create')->willReturnCallback(
            function ($middlewares, $closure) {
                $requestHandler = $this->createMock(RequestHandlerInterface::class);
                $requestHandler->method('handle')->willReturnCallback(
                    function () use ($closure) { return $closure(); }
                );
                return $requestHandler;
            }
        );

        // Create a mocked dispatcher
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->method('dispatch')->willReturn([
            Dispatcher::NOT_FOUND
        ]);

        // Inject the mocked dispatcher in the router
        $reflection = new ReflectionClass($this->fastRoute);
        $property = $reflection->getProperty('dispatcher');
        $property->setAccessible(true);
        $property->setValue($this->fastRoute, $dispatcher);

        // Create mocked incoming server request
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($this->getMockedUri('/notfound'));

        // Act + Assert
        $this->expectException(\Rogue\Mantle\Http\Exceptions\NotFoundException::class);
        $this->fastRoute->handle($request);
    }

    public function testDispatchMethodNotAllowed(): void
    {
        // Arrange

        // Capture the closure passed to the middleware dispatcher factory
        $this->middlewareDispatcherFactory->method('create')->willReturnCallback(
            function ($middlewares, $closure) {
                $requestHandler = $this->createMock(RequestHandlerInterface::class);
                $requestHandler->method('handle')->willReturnCallback(
                    function () use ($closure) { return $closure(); }
                );
                return $requestHandler;
            }
        );

        // Create mocked incoming server request
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getUri')->willReturn($this->getMockedUri('/foo'));

        // Create a mocked dispatcher
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->method('dispatch')->willReturn([
            Dispatcher::METHOD_NOT_ALLOWED,
            ['GET', 'PUT']
        ]);

        // Inject the mocked dispatcher in the router
        $reflection = new ReflectionClass($this->fastRoute);
        $property = $reflection->getProperty('dispatcher');
        $property->setAccessible(true);
        $property->setValue($this->fastRoute, $dispatcher);

        $httpStatus = null;
        $allowHeader = null;

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withStatus')->willReturnCallback(
            function ($status) use (&$httpStatus, $response) {
                $httpStatus = $status;
                return $response;
            }
        );
        $response->method('withHeader')->willReturnCallback(
            function ($header, $value) use (&$allowHeader, $response) {
                $allowHeader = "{$header}: {$value}";
                return $response;
            }
        );

        Response::setInstance($response);

        // Act + Assert
        try {

            $this->fastRoute->handle($request);
            $this->fail('Expected MethodNotAllowedException was not thrown');

        } catch (MethodNotAllowedException $e) {

            $this->assertSame($response, $e->getResponse());
            $this->assertSame(HttpStatus::METHOD_NOT_ALLOWED->value, $httpStatus);
            $this->assertSame('Allow: GET, PUT', $allowHeader);

        }
    }

    public static function httpMethodProvider(): Generator
    {
        foreach (HttpMethod::cases() as $httpMethod) {
            yield strtolower($httpMethod->value) => [$httpMethod];
        }
    }

    private function getMockedUri(string $path): UriInterface
    {
        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->method('getPath')->willReturn($path);
        $uriMock->method('__toString')->willReturn($path);
        return $uriMock;
    }
}
