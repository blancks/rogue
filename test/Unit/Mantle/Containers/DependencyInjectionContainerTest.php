<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Containers;

use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Containers\DependencyInjectionContainer;

class DependencyInjectionContainerTest extends TestCase
{
    public function testBindAndMakeResolvesConcreteClass(): void
    {
        // Arrange
        $container = new DependencyInjectionContainer();

        // Act
        $container->bind(FooInterface::class, Foo::class);
        $foo = $container->make(FooInterface::class);

        // Assert
        $this->assertInstanceOf(Foo::class, $foo);
        $this->assertInstanceOf(FooInterface::class, $foo);
    }

    public function testMakeResolvesDependenciesRecursively(): void
    {
        // Arrange
        $container = new DependencyInjectionContainer();

        // Act
        $container->bind(FooInterface::class, Foo::class);
        $container->bind(BarInterface::class, Bar::class);
        $bar = $container->make(BarInterface::class);

        // Assert
        $this->assertInstanceOf(Bar::class, $bar);
        $this->assertInstanceOf(FooInterface::class, $bar->foo);
    }

    public function testMakeThrowsExceptionForNonExistentClass(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Class NonExistentClass does not exist.');

        $container = new DependencyInjectionContainer();
        $container->make('NonExistentClass');
    }

    public function testCallInjectsDependencies(): void
    {
        // Arrange
        $container = new DependencyInjectionContainer();
        $container->bind(FooInterface::class, Foo::class);

        // Act
        $result = $container->call(function (FooInterface $foo) {
            return $foo->bar();
        });

        // Assert
        $this->assertEquals('bar', $result);
    }

    public function testCallWithParametersOverridesDependency(): void
    {
        // Arrange
        $container = new DependencyInjectionContainer();
        $foo = new Foo();

        // Act
        $result = $container->call(function (FooInterface $foo) {
            return $foo->bar();
        }, ['foo' => $foo]);

        // Assert
        $this->assertEquals('bar', $result);
    }
}

interface FooInterface
{
    public function bar(): string;
}

class Foo implements FooInterface
{
    public function bar(): string
    {
        return 'bar';
    }
}

interface BarInterface
{
}

class Bar implements BarInterface
{
    public function __construct(public FooInterface $foo)
    {
    }
}
