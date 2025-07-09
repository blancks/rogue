<?php

declare(strict_types=1);

namespace Test\Integration\Mantle\Routing\Wrappers;

use Generator;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Psr7\ServerRequest as GuzzleServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Mantle\Aspects\Container;
use Mantle\Aspects\EventDispatcher;
use Mantle\Aspects\Response;
use Mantle\Containers\PhpDiContainer;
use Mantle\Contracts\RouterInterface;
use Mantle\Events\EventDispatcher as EventsEventDispatcher;
use Mantle\Http\HttpMethod;
use Mantle\Http\HttpStatus;
use Mantle\Http\Middlewares\ExceptionHandlerMiddleware;
use Mantle\Routing\Handlers\MiddlewareDispatcher;
use Mantle\Routing\Handlers\MiddlewareDispatcherFactory;
use Mantle\Routing\UnmaskedRouteDiscovery;
use Mantle\Routing\Wrappers\FastRoute;

class FastRouteIntegrationTest extends TestCase
{
    private static string $testMiddlewareFilename;
    private static string $testMaskControllerFilename;
    private static string $testAppControllerFilename;
    private static string $maskDir;
    private static string $appDir;

    private array $cachedServerVars = [];

    public static function setUpBeforeClass(): void
    {
        static::$testMaskControllerFilename = tmpPath('TestMaskNamespace/TestController.php');
        static::$testAppControllerFilename = tmpPath('TestAppNamespace/TestController.php');
        static::$testMiddlewareFilename = tmpPath('TestAppNamespace/XSomethingHeader.php');
        static::$maskDir = dirname(static::$testMaskControllerFilename);
        static::$appDir = dirname(static::$testAppControllerFilename);

        if (!is_dir(static::$maskDir)) {
            mkdir(static::$maskDir, recursive: true);
        }

        if (!is_dir(static::$appDir)) {
            mkdir(static::$appDir, recursive: true);
        }

        file_put_contents(
            static::$testMaskControllerFilename,
            <<<PHP
            <?php
                namespace TestMaskNamespace;
                use Mantle\Routing\Attributes\Route;
                use Mantle\Routing\Attributes\UnmaskedRoute;
                use Mantle\Http\HttpMethod;
                use TestAppNamespace\XSomethingHeader;

                class TestController {
                    #[Route(method: HttpMethod::GET, path: '/route/{method}')]
                    #[Route(method: HttpMethod::POST, path: '/route/{method}')]
                    #[Route(method: HttpMethod::DELETE, path: '/route/{method}')]
                    #[Route(method: HttpMethod::PUT, path: '/route/{method}')]
                    #[Route(method: HttpMethod::PATCH, path: '/route/{method}')]
                    #[Route(method: HttpMethod::OPTIONS, path: '/route/{method}')]
                    #[Route(method: HttpMethod::HEAD, path: '/route/{method}')]
                    public function route(string \$method) {
                        return \$method;
                    }

                    #[UnmaskedRoute(method: HttpMethod::GET, path: '/unmaskedroute/{method}', middleware:[XSomethingHeader::class])]
                    #[UnmaskedRoute(method: HttpMethod::POST, path: '/unmaskedroute/{method}')]
                    #[UnmaskedRoute(method: HttpMethod::DELETE, path: '/unmaskedroute/{method}')]
                    #[UnmaskedRoute(method: HttpMethod::PUT, path: '/unmaskedroute/{method}')]
                    #[UnmaskedRoute(method: HttpMethod::PATCH, path: '/unmaskedroute/{method}')]
                    #[UnmaskedRoute(method: HttpMethod::OPTIONS, path: '/unmaskedroute/{method}')]
                    #[UnmaskedRoute(method: HttpMethod::HEAD, path: '/unmaskedroute/{method}')]
                    public function unmasked(string \$method) {
                        return 'U'.\$method;
                    }
                }
            PHP
        );

        file_put_contents(
            static::$testAppControllerFilename,
            <<<PHP
            <?php
                namespace TestAppNamespace;
                use Mantle\Routing\Attributes\{Get, Post, Delete, Put, Patch, Options, Head};
                use Mantle\Http\HttpMethod;

                class TestController {
                    public function route() {
                        return 'This method should not be unmasked';
                    }

                    #[Get('/approute/{method}')]
                    #[Post('/approute/{method}')]
                    #[Delete('/approute/{method}')]
                    #[Put('/approute/{method}')]
                    #[Patch('/approute/{method}')]
                    #[Options('/approute/{method}')]
                    #[Head('/approute/{method}')]
                    public function unmasked(string \$method) {
                        return 'A'.\$method;
                    }

                    #[Post('/fooroute', middleware:[XSomethingHeader::class])]
                    #[Delete('/fooroute')]
                    public function app(): array {
                        return [1,2,3];
                    }
                }
            PHP
        );

        file_put_contents(
            static::$testMiddlewareFilename,
            <<<PHP
            <?php
                namespace TestAppNamespace;
                use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
                use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

                final class XSomethingHeader implements MiddlewareInterface
                {
                    public function process(
                        ServerRequestInterface \$request,
                        RequestHandlerInterface \$handler
                    ): ResponseInterface {
                        \$response = \$handler->handle(\$request);
                        return \$response->withAddedHeader('X-Something', 'foobar header value');
                    }
                }
            PHP
        );
    }

    public static function tearDownAfterClass(): void
    {
        @unlink(static::$testMaskControllerFilename);
        @unlink(static::$testAppControllerFilename);
        @unlink(static::$testMiddlewareFilename);
        @rmdir(static::$maskDir);
        @rmdir(static::$appDir);
    }

    public function setUp(): void
    {
        if (
            !class_exists('\\GuzzleHttp\\Psr7\\ServerRequest')
            || !class_exists('\\GuzzleHttp\\Psr7\\Response')
        ) {
            $this->markTestSkipped('GuzzleHttp is not available');
            return;
        }

        $this->cachedServerVars = [
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
        ];
    }

    public function tearDown(): void
    {
        foreach ($this->cachedServerVars as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }

    #[DataProvider('httpMethodProvider')]
    public function testImmutableRouteOnMaskController(HttpMethod $httpMethod): void
    {
        // Arrange
        $this->setUpCore();

        $serverRequest = $this->makeServerRequest(
            method: $httpMethod->value,
            path: '/route/'. $httpMethod->value
        );

        $router = $this->newFastRouteInstance();
        $router->routeDiscovery(
            rootNamespace: 'TestMaskNamespace',
            rootPath: static::$maskDir,
            namespacePaths: [
                'TestAppNamespace' => static::$appDir,
            ]
        );

        // Act
        $response = $router->handle($serverRequest);

        // Asserts
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(HttpStatus::OK->value, $response->getStatusCode());
        $this->assertSame('application/json;charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame(json_encode($httpMethod->value), (string)$response->getBody());
    }

    #[DataProvider('httpMethodProvider')]
    public function testOverrideableRouteOnMaskController(HttpMethod $httpMethod): void
    {
        // Arrange
        $this->setUpCore();

        $serverRequest = $this->makeServerRequest(
            method: $httpMethod->value,
            path: '/unmaskedroute/'. $httpMethod->value
        );

        $router = $this->newFastRouteInstance();
        $router->routeDiscovery(
            rootNamespace: 'TestMaskNamespace',
            rootPath: static::$maskDir,
            namespacePaths: [
                'TestAppNamespace' => static::$appDir,
            ]
        );

        // Act
        $response = $router->handle($serverRequest);

        // Asserts
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(HttpStatus::OK->value, $response->getStatusCode());
        $this->assertSame('application/json;charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame(json_encode('A'. $httpMethod->value), (string)$response->getBody());
    }

    #[DataProvider('httpMethodProvider')]
    public function testAdditionalRouteOnAppController(HttpMethod $httpMethod): void
    {
        // Arrange
        $this->setUpCore();

        $serverRequest = $this->makeServerRequest(
            method: $httpMethod->value,
            path: '/approute/'. $httpMethod->value
        );

        $router = $this->newFastRouteInstance();
        $router->routeDiscovery(
            rootNamespace: 'TestMaskNamespace',
            rootPath: static::$maskDir,
            namespacePaths: [
                'TestAppNamespace' => static::$appDir,
            ]
        );

        // Act
        $response = $router->handle($serverRequest);

        // Asserts
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(HttpStatus::OK->value, $response->getStatusCode());
        $this->assertSame('application/json;charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame(json_encode('A'. $httpMethod->value), (string)$response->getBody());
    }

    #[DataProvider('nonRoutedHttpMethodProvider')]
    public function testMissingRouteForHttpMethod(HttpMethod $httpMethod): void
    {
        // Arrange
        $this->setUpCore();

        $serverRequest = $this->makeServerRequest(
            method: $httpMethod->value,
            path: '/fooroute'
        );

        $router = $this->newFastRouteInstance();
        $router->routeDiscovery(
            rootNamespace: 'TestMaskNamespace',
            rootPath: static::$maskDir,
            namespacePaths: [
                'TestAppNamespace' => static::$appDir,
            ]
        );

        // Act
        $response = $router->handle($serverRequest);

        // Asserts
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(HttpStatus::METHOD_NOT_ALLOWED->value, $response->getStatusCode());
        $this->assertSame('POST, DELETE', $response->getHeaderLine('Allow'));
    }

    public function testRouteMiddleware(): void
    {
        // Arrange
        $this->setUpCore();

        $serverRequest = $this->makeServerRequest(
            method: HttpMethod::POST->value,
            path: '/fooroute'
        );

        $router = $this->newFastRouteInstance();
        $router->routeDiscovery(
            rootNamespace: 'TestMaskNamespace',
            rootPath: static::$maskDir,
            namespacePaths: [
                'TestAppNamespace' => static::$appDir,
            ]
        );

        // Act
        $response = $router->handle($serverRequest);

        // Asserts
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(HttpStatus::OK->value, $response->getStatusCode());
        $this->assertSame('foobar header value', $response->getHeaderLine('X-Something'));
    }

    public function testUnmaskedRouteMiddleware(): void
    {
        // Arrange
        $this->setUpCore();

        $serverRequest = $this->makeServerRequest(
            method: HttpMethod::GET->value,
            path: '/unmaskedroute/'. HttpMethod::GET->value
        );

        $router = $this->newFastRouteInstance();
        $router->routeDiscovery(
            rootNamespace: 'TestMaskNamespace',
            rootPath: static::$maskDir,
            namespacePaths: [
                'TestAppNamespace' => static::$appDir,
            ]
        );

        // Act
        $response = $router->handle($serverRequest);

        // Asserts
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(HttpStatus::OK->value, $response->getStatusCode());
        $this->assertSame('foobar header value', $response->getHeaderLine('X-Something'));
    }

    public static function nonRoutedHttpMethodProvider(): Generator
    {
        foreach ([HttpMethod::GET, HttpMethod::PUT, HttpMethod::PATCH, HttpMethod::OPTIONS, HttpMethod::HEAD] as $httpMethod) {
            yield strtolower($httpMethod->value) => [$httpMethod];
        }
    }

    public static function httpMethodProvider(): Generator
    {
        foreach (HttpMethod::cases() as $httpMethod) {
            yield strtolower($httpMethod->value) => [$httpMethod];
        }
    }

    private function newFastRouteInstance(): RouterInterface
    {
        $router = new FastRoute(
            Container::getInstance(),
            EventDispatcher::getInstance(),
            new UnmaskedRouteDiscovery(),
            new MiddlewareDispatcherFactory(
                MiddlewareDispatcher::class,
                Container::getInstance()
            )
        );

        $router->addMiddleware(ExceptionHandlerMiddleware::class);
        return $router;
    }

    private function makeServerRequest(string $method, string $path): ServerRequestInterface
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $path;
        $_SERVER['HTTP_HOST'] = 'localhost';

        return GuzzleServerRequest::fromGlobals();
    }

    private function setUpCore(): void
    {
        Container::setInstance(new PhpDiContainer());
        EventDispatcher::setInstance(new EventsEventDispatcher());
        Response::setInstance(new GuzzleResponse());
    }
}
