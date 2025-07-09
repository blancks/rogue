<?php

declare(strict_types=1);

namespace Test\Unit\Mantle\Containers;

use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Mantle\Containers\PhpDiContainer;

class PhpDiContainerTest extends TestCase
{
    public function testGetReturnsStoredEntry(): void
    {
        // Arrange
        $foo = new Foo();
        $container = new PhpDiContainer();
        $container->bind(FooInterface::class, Foo::class);

        // Act
        $result = $container->get(FooInterface::class);

        // Assert
        $this->assertInstanceOf(Foo::class, $result);
        $this->assertInstanceOf(FooInterface::class, $result);
    }

    public function testGetThrowsExceptionForNonExistentEntry(): void
    {
        // Arrange
        $container = new PhpDiContainer();

        // Assert
        $this->expectException(NotFoundExceptionInterface::class);

        // Act
        $container->get('NonExistentEntry');
    }

    public function testHasReturnsTrueForExistingEntry(): void
    {
        // Arrange
        $container = new PhpDiContainer();
        $container->bind(FooInterface::class, Foo::class);

        // Act
        $result = $container->has(FooInterface::class);

        // Assert
        $this->assertTrue($result);
    }

    public function testHasReturnsFalseForNonExistentEntry(): void
    {
        // Arrange
        $container = new PhpDiContainer();

        // Act
        $result = $container->has('NonExistentEntry');

        // Assert
        $this->assertFalse($result);
    }

    public function testBindAndMakeResolvesConcreteClass(): void
    {
        // Arrange
        $container = new PhpDiContainer();

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
        $container = new PhpDiContainer();

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
        $this->expectException(NotFoundExceptionInterface::class);

        $container = new PhpDiContainer();
        $container->make('NonExistentClass');
    }

    public function testCallInjectsDependencies(): void
    {
        // Arrange
        $container = new PhpDiContainer();
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
        $container = new PhpDiContainer();
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
