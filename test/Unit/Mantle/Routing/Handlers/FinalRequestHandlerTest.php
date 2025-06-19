<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Routing\Handlers;

use LogicException;
use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Routing\Handlers\FinalRequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FinalRequestHandlerTest extends TestCase
{
    public function testHandleReturnsValidResponse()
    {
        // Arrange
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockResponseOutput = $this->createMock(ResponseInterface::class);
        $mockResponseFallback = $this->createMock(ResponseInterface::class);

        // Act
        $handler = new FinalRequestHandler(fn () => $mockResponseOutput, $mockResponseFallback);
        $result = $handler->handle($mockRequest);

        // Assert
        $this->assertSame($mockResponseOutput, $result);
    }

    public function testHandleReturnsResponseForSerializableOutput()
    {
        // Arrange
        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('withStatus')->willReturnSelf();
        $mockResponse->method('withHeader')->willReturnSelf();
        $mockResponse->method('withBody')->willReturnSelf();

        // Act
        $handler = new FinalRequestHandler(fn () => ['foo' => 'bar'], $mockResponse);
        $result = $handler->handle($mockRequest);

        // Assert
        $this->assertSame($mockResponse, $result);
    }

    public function testHandleThrowsLogicExceptionOnInvalidJson()
    {
        // Arrange
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        // Act + Assert
        $handler = new FinalRequestHandler(function () {
            try {
                // this cannot be serialized to json
                $fopen = fopen('php://memory', 'rb');
                return $fopen;
            } finally {
                fclose($fopen);
            }
        }, $mockResponse);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'The return type of a controller must be either '
            . 'an instance of PSR-7 ResponseInterface or a '
            . 'JSON-serializable value.'
        );
        $handler->handle($mockRequest);
    }
}
