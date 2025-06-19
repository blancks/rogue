<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Routing\Handlers;

use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Routing\Handlers\MiddlewareDispatcher;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ValueError;

class MiddlewareDispatcherTest extends TestCase
{
    public function testHandleWithNoMiddlewareReturnsFinalHandlerResponse()
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
        $dispatcher = new MiddlewareDispatcher([], $finalHandler);
        $result = $dispatcher->handle($request);

        // Assert
        $this->assertSame($response, $result);
    }

    public function testHandleWithMiddlewareProcessesStack()
    {
        // Arrange
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $finalHandler = $this->createMock(RequestHandlerInterface::class);
        $finalHandler
            ->expects($this->never())
            ->method('handle');

        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware
            ->expects($this->once())
            ->method('process')
            ->with($request, $this->isInstanceOf(MiddlewareDispatcher::class))
            ->willReturn($response);

        // Act
        $dispatcher = new MiddlewareDispatcher([$middleware], $finalHandler);
        $result = $dispatcher->handle($request);

        // Assert
        $this->assertSame($response, $result);
    }

    public function testConstructorThrowsOnInvalidMiddleware()
    {
        // Arrange
        $finalHandler = $this->createMock(RequestHandlerInterface::class);
        $invalidMiddleware = new class () {};

        // Act + Assert
        $this->expectException(ValueError::class);
        new MiddlewareDispatcher([$invalidMiddleware], $finalHandler);
    }
}
