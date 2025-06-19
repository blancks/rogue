<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Routing\Attributes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Http\HttpMethod;
use Rogue\Mantle\Routing\Attributes\UnmaskedRoute;

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

    public static function routeMethodProvider(): \Generator
    {
        foreach (HttpMethod::cases() as $httpMethod) {
            yield strtolower($httpMethod->name) => [$httpMethod];
        }
    }
}
