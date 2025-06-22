<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Routing\Handlers;

use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Routing\Handlers\MiddlewareDispatcher;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rogue\Mantle\Contracts\ContainerInterface;
use ValueError;

class MiddlewareDispatcherTest extends TestCase
{
    public function testImplementsInterfaces(): void
    {
        $dispatcher = new MiddlewareDispatcher();
        $this->assertInstanceOf(\Rogue\Mantle\Contracts\MiddlewareDispatcherInterface::class, $dispatcher);
        $this->assertInstanceOf(\Rogue\Mantle\Contracts\ContainerAwareInterface::class, $dispatcher);
    }

    public function testHandleWithNoMiddlewareReturnsFinalHandlerResponse(): void
    {
        // Arrange
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $finalHandler = $this->createMock(RequestHandlerInterface::class);
        $finalHandler
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        // Act
        $dispatcher = new MiddlewareDispatcher();
        $dispatcher->setMiddlewareStack([]);
        $dispatcher->setFinalHandler($finalHandler);
        $result = $dispatcher->handle($request);

        // Assert
        $this->assertSame($response, $result);
    }

    public function testHandleWithMiddlewareProcessesStack(): void
    {
        // Arrange
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $finalHandler = $this->createMock(RequestHandlerInterface::class);
        $finalHandler->expects($this->never())->method('handle');

        $middlewareClass = 'TestMiddleware';
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())
            ->method('process')
            ->with($request, $this->isInstanceOf(MiddlewareDispatcher::class))
            ->willReturn($response);

        $container = $this->getMockContainerInstance($middleware);

        // Act
        $dispatcher = new MiddlewareDispatcher();
        $dispatcher->setContainer($container);
        $dispatcher->setMiddlewareStack([$middlewareClass]);
        $dispatcher->setFinalHandler($finalHandler);
        $result = $dispatcher->handle($request);

        // Assert
        $this->assertSame($response, $result);
    }

    public function testConstructorThrowsOnInvalidMiddleware(): void
    {
        // Arrange
        $finalHandler = $this->createMock(RequestHandlerInterface::class);
        $invalidClass = 'InvalidMiddleware';
        $invalidMiddleware = new class () {};
        $container = $this->getMockContainerInstance($invalidMiddleware);

        $dispatcher = new MiddlewareDispatcher();
        $dispatcher->setContainer($container);
        $dispatcher->setFinalHandler($finalHandler);

        // Act + Assert
        $this->expectException(ValueError::class);
        $dispatcher->setMiddlewareStack([$invalidClass]);
    }

    private function getMockContainerInstance(object $middleware): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('make')->willReturn($middleware);
        return $container;
    }
}
