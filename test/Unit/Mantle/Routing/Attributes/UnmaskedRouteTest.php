<?php

declare(strict_types=1);

namespace Test\Unit\Mantle\Routing\Attributes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Mantle\Http\HttpMethod;
use Mantle\Routing\Attributes\UnmaskedRoute;

class UnmaskedRouteTest extends TestCase
{
    public function testRouteReturnsPath(): void
    {
        $route = new UnmaskedRoute(HttpMethod::GET, '/item/{id}');

        $this->assertSame(
            expected: '/item/{id}',
            actual: $route->getPath(),
        );
    }

    #[DataProvider('routeMethodProvider')]
    public function testRouteReturnsMethod(HttpMethod $method): void
    {
        $route = new UnmaskedRoute($method, '/');

        $this->assertSame(
            expected: $method,
            actual: $route->getMethod()
        );
    }

    public function testRouteAcceptsAndReturnsMiddlewares(): void
    {
        $middlewares = ['auth', 'throttle'];
        $route = new UnmaskedRoute(HttpMethod::POST, '/foo', $middlewares);
        $this->assertSame($middlewares, $route->getMiddleware());
    }

    #[DataProvider('routeMethodProvider')]
    public function testAttributeClassChild(HttpMethod $method): void
    {
        // Arrange
        $middlewares = ['one', 'two', 'three'];
        $className = '\\Mantle\\Routing\\Attributes\\U'. ucwords(strtolower($method->value));

        // Act + Asserts
        $this->assertTrue(class_exists($className), 'Attribute does not exist');

        $route = new $className('/foo/{bar}', $middlewares);

        $this->assertSame($method, $route->getMethod());
        $this->assertSame('/foo/{bar}', $route->getPath());
        $this->assertSame($middlewares, $route->getMiddleware());
    }

    public static function routeMethodProvider(): \Generator
    {
        foreach (HttpMethod::cases() as $httpMethod) {
            yield strtolower($httpMethod->name) => [$httpMethod];
        }
    }
}
