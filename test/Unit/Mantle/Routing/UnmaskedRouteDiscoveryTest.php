<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Routing;

use PHPUnit\Framework\TestCase;

class UnmaskedRouteDiscoveryTest extends TestCase
{
    private static string $testMaskControllerFilename;
    private static string $testAppControllerFilename;
    private static string $maskDir;
    private static string $appDir;

    public static function setUpBeforeClass(): void
    {
        static::$testMaskControllerFilename = tmpPath('TestMaskNamespace2/TestController.php');
        static::$testAppControllerFilename = tmpPath('TestAppNamespace2/TestController.php');
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
                namespace TestMaskNamespace2;
                use Rogue\Mantle\Routing\Attributes\Route;
                use Rogue\Mantle\Routing\Attributes\UnmaskedRoute;
                use Rogue\Mantle\Http\HttpMethod;

                class TestController {
                    #[Route(method: HttpMethod::GET, path: '/test')]
                    public function index() {}
                    #[Route(method: HttpMethod::GET, path: '/test/foo')]
                    #[Route(method: HttpMethod::GET, path: '/test/bar')]
                    public function foobar() {}
                    #[UnmaskedRoute(method: HttpMethod::POST, path: '/unmasked')]
                    public function unmasked() {}
                }
            PHP
        );

        file_put_contents(
            static::$testAppControllerFilename,
            <<<PHP
            <?php
                namespace TestAppNamespace2;

                class TestController {
                    public function unmasked() {}
                }
            PHP
        );
    }

    public static function tearDownAfterClass(): void
    {
        @unlink(static::$testMaskControllerFilename);
        @unlink(static::$testAppControllerFilename);
        @rmdir(static::$maskDir);
        @rmdir(static::$appDir);
    }

    public function testDiscoversRouteAttribute(): void
    {
        // Arrange
        $discovery = new \Rogue\Mantle\Routing\UnmaskedRouteDiscovery();

        // Act
        $foundRoute1Action = false;
        $foundRoute2Action = false;
        $routes = $discovery->discover('TestMaskNamespace2', static::$maskDir);

        foreach ($routes as $route) {
            if (
                $route['path'] === '/test'
                && $route['method'] === \Rogue\Mantle\Http\HttpMethod::GET
            ) {
                $foundRoute1Action = $route['action'];
            }

            if (
                $route['path'] === '/test/foo'
                && $route['method'] === \Rogue\Mantle\Http\HttpMethod::GET
            ) {
                $foundRoute2Action = $route['action'];
            }
        }

        // Assert
        $this->assertNotFalse($foundRoute1Action, 'Route attribute for "/test" not discovered');
        $this->assertNotFalse($foundRoute2Action, 'Route attribute for "/test/foo" not discovered');
        $this->assertSame(['TestMaskNamespace2\\TestController', 'index'], $foundRoute1Action);
        $this->assertSame(['TestMaskNamespace2\\TestController', 'foobar'], $foundRoute2Action);
    }

    public function testDiscoversUnmaskedRouteAttribute(): void
    {
        // Arrange
        $discovery = new \Rogue\Mantle\Routing\UnmaskedRouteDiscovery();

        // Act
        $foundRouteAction = false;
        $routes = $discovery->discover('TestMaskNamespace2', static::$maskDir);

        foreach ($routes as $route) {
            if (
                $route['path'] === '/unmasked'
                && $route['method'] === \Rogue\Mantle\Http\HttpMethod::POST
            ) {
                $foundRouteAction = $route['action'];
                break;
            }
        }

        // Assert
        $this->assertNotFalse($foundRouteAction, 'UnmaskedRoute attribute for "/unmasked" not discovered');
        $this->assertSame(['TestMaskNamespace2\\TestController', 'unmasked'], $foundRouteAction);
    }

    public function testDiscoversMultipleRouteAttributeOnSameClassMethod(): void
    {
        // Arrange
        $discovery = new \Rogue\Mantle\Routing\UnmaskedRouteDiscovery();

        // Act
        $foundRoute1Action = false;
        $foundRoute2Action = false;
        $routes = $discovery->discover('TestMaskNamespace2', static::$maskDir);

        foreach ($routes as $route) {
            if (
                $route['path'] === '/test/foo'
                && $route['method'] === \Rogue\Mantle\Http\HttpMethod::GET
            ) {
                $foundRoute1Action = $route['action'];
            }

            if (
                $route['path'] === '/test/bar'
                && $route['method'] === \Rogue\Mantle\Http\HttpMethod::GET
            ) {
                $foundRoute2Action = $route['action'];
            }
        }

        // Assert
        $this->assertNotFalse($foundRoute1Action, 'Route attribute for "/test/foo" not discovered');
        $this->assertNotFalse($foundRoute2Action, 'Route attribute for "/test/bar" not discovered');
        $this->assertSame(['TestMaskNamespace2\\TestController', 'foobar'], $foundRoute1Action);
        $this->assertSame(['TestMaskNamespace2\\TestController', 'foobar'], $foundRoute2Action);
    }

    public function testUnmaskedRouteIsResolvedToAppNamespace(): void
    {
        // Arrange
        $discovery = new \Rogue\Mantle\Routing\UnmaskedRouteDiscovery();

        // Act
        $foundRouteAction = false;
        $routes = $discovery->discover(
            'TestMaskNamespace2',
            static::$maskDir,
            ['TestAppNamespace2' => static::$appDir]
        );

        foreach ($routes as $route) {
            if (
                $route['path'] === '/unmasked'
                && $route['method'] === \Rogue\Mantle\Http\HttpMethod::POST
            ) {
                $foundRouteAction = $route['action'];
                break;
            }
        }

        // Assert
        $this->assertNotFalse($foundRouteAction, 'UnmaskedRoute attribute for "/unmasked" not discovered');
        $this->assertSame(['TestAppNamespace2\\TestController', 'unmasked'], $foundRouteAction);
    }
}
