<?php

declare(strict_types=1);

namespace Test\Integration\Mantle\Routing\Handlers;

use PHPUnit\Framework\TestCase;
use Mantle\Routing\Handlers\FinalRequestHandler;
use Mantle\Http\HttpStatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Mantle\Http\HttpMethod;

class FinalRequestHandlerIntegrationTest extends TestCase
{
    private string $serverRequestClass;
    private string $responseClass;

    public function setUp(): void
    {
        if (
            !class_exists('\\GuzzleHttp\\Psr7\\ServerRequest')
            || !class_exists('\\GuzzleHttp\\Psr7\\Response')
        ) {
            $this->markTestSkipped('GuzzleHttp is not available');
            return;
        }

        $this->serverRequestClass = \GuzzleHttp\Psr7\ServerRequest::class;
        $this->responseClass = \GuzzleHttp\Psr7\Response::class;
    }

    public function getServerRequestInstance(HttpMethod $httpMethod, string $path): ServerRequestInterface
    {
        /** @var ServerRequestInterface $serverRequest */
        $serverRequest = new ($this->serverRequestClass)($httpMethod->value, $path);
        return $serverRequest;
    }

    public function getResponseInstance(
        HttpStatus $httpStatus = HttpStatus::OK,
        array $headers = [],
        string $body = ''
    ): ResponseInterface {
        /** @var ResponseInterface $response */
        $response = new ($this->responseClass)($httpStatus->getCode(), $headers, $body);
        return $response;
    }

    public function testHandleReturnsJsonResponseWithSerializableData()
    {
        // Arrange
        $request = $this->getServerRequestInstance(HttpMethod::GET, '/test');
        $baseResponse = $this->getResponseInstance();
        $output = ['foo' => 'bar', 'baz' => 123];

        // Act
        $handler = new FinalRequestHandler(fn () => $output, $baseResponse);
        $response = $handler->handle($request);
        $body = (string) $response->getBody();

        // Assert
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(HttpStatus::OK->getCode(), $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-type'));
        $this->assertJson($body);
        $this->assertSame($output, json_decode($body, true));
    }

    public function testHandleReturnsResponseInstanceDirectly()
    {
        // Arrange
        $httpStatus = HttpStatus::CREATED;

        $request = $this->getServerRequestInstance(HttpMethod::POST, '/test');
        $fallbackResponse = $this->getResponseInstance();

        $customResponse = $this->getResponseInstance(
            httpStatus: $httpStatus,
            headers: ['X-Test' => 'yes'],
            body: 'Something, Something, Something, Dark Side'
        );

        // Act
        $handler = new FinalRequestHandler(fn () => $customResponse, $fallbackResponse);
        $response = $handler->handle($request);

        // Assert
        $this->assertSame($customResponse, $response);
        $this->assertSame($httpStatus->getCode(), $response->getStatusCode());
        $this->assertSame('yes', $response->getHeaderLine('X-Test'));
        $this->assertSame('Something, Something, Something, Dark Side', (string) $response->getBody());
    }
}
