<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Routing;

use PHPUnit\Framework\TestCase;

class UnmaskedRouteDiscoveryTest extends TestCase
{
    private string $maskDir;
    private string $appDir;
    private string $testMaskControllerFilename;
    private string $testAppControllerFilename;

    protected function setUp(): void
    {
        $this->testMaskControllerFilename = tmpPath('TestMaskNamespace/TestController.php');
        $this->testAppControllerFilename = tmpPath('TestAppNamespace/TestController.php');

        $this->maskDir = dirname($this->testMaskControllerFilename);
        $this->appDir = dirname($this->testAppControllerFilename);

        if (!is_dir($this->maskDir)) {
            mkdir($this->maskDir);
        }

        if (!is_dir($this->appDir)) {
            mkdir($this->appDir);
        }

        file_put_contents(
            $this->testMaskControllerFilename,
            <<<PHP
            <?php
                namespace TestMaskNamespace;
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
            $this->testAppControllerFilename,
            <<<PHP
            <?php
                namespace TestAppNamespace;

                class TestController {
                    public function unmasked() {}
                }
            PHP
        );
    }

    protected function tearDown(): void
    {
        @unlink($this->testMaskControllerFilename);
        @unlink($this->testAppControllerFilename);
        @rmdir($this->maskDir);
        @rmdir($this->appDir);
    }

    public function testDiscoversRouteAttribute(): void
    {
        // Arrange
        $discovery = new \Rogue\Mantle\Routing\UnmaskedRouteDiscovery();

        // Act
        $foundRoute1Action = false;
        $foundRoute2Action = false;
        $routes = $discovery->discover('TestMaskNamespace', $this->maskDir);

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
        $this->assertSame(['TestMaskNamespace\\TestController', 'index'], $foundRoute1Action);
        $this->assertSame(['TestMaskNamespace\\TestController', 'foobar'], $foundRoute2Action);
    }

    public function testDiscoversUnmaskedRouteAttribute(): void
    {
        // Arrange
        $discovery = new \Rogue\Mantle\Routing\UnmaskedRouteDiscovery();

        // Act
        $foundRouteAction = false;
        $routes = $discovery->discover('TestMaskNamespace', $this->maskDir);

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
        $this->assertSame(['TestMaskNamespace\\TestController', 'unmasked'], $foundRouteAction);
    }

    public function testDiscoversMultipleRouteAttributeOnSameClassMethod(): void
    {
        // Arrange
        $discovery = new \Rogue\Mantle\Routing\UnmaskedRouteDiscovery();

        // Act
        $foundRoute1Action = false;
        $foundRoute2Action = false;
        $routes = $discovery->discover('TestMaskNamespace', $this->maskDir);

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
        $this->assertSame(['TestMaskNamespace\\TestController', 'foobar'], $foundRoute1Action);
        $this->assertSame(['TestMaskNamespace\\TestController', 'foobar'], $foundRoute2Action);
    }

    public function testUnmaskedRouteIsResolvedToAppNamespace(): void
    {
        // Arrange
        $discovery = new \Rogue\Mantle\Routing\UnmaskedRouteDiscovery();

        // Act
        $foundRouteAction = false;
        $routes = $discovery->discover(
            'TestMaskNamespace',
            $this->maskDir,
            ['TestAppNamespace' => $this->appDir]
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
        $this->assertSame(['TestAppNamespace\\TestController', 'unmasked'], $foundRouteAction);
    }
}
