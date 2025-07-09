<?php

declare(strict_types=1);

namespace Mantle\Aspects;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Mantle\Contracts\Traits\SingletonTrait;
use Mantle\Http\HttpStatus;

/**
 * Singleton wrapper and utility for PSR-7 ResponseInterface.
 *
 * Provides static accessors and helpers for HTTP response data,
 * using a singleton instance of a PSR-7 Response implementation.
 */
class Response
{
    use SingletonTrait;

    private static ResponseInterface $instance;

    /**
     * Set the PSR-7 Response implementation to use.
     *
     * @param ResponseInterface $response
     * @return void
     */
    public static function setInstance(ResponseInterface $response): void
    {
        self::$instance = $response;
    }

    /**
     * Get the singleton PSR-7 Response instance.
     */
    public static function getInstance(): ResponseInterface
    {
        return self::$instance;
    }

    /**
     * Retrieve the HTTP status code.
     * @return int
     */
    public static function getStatusCode(): int
    {
        return self::$instance->getStatusCode();
    }

    /**
     * Retrieve the reason phrase associated with the status code.
     * @return string
     */
    public static function getReasonPhrase(): string
    {
        return self::$instance->getReasonPhrase();
    }

    /**
     * Retrieve all response headers.
     * @return array
     * @phpstan-return array<string, string[]>
     */
    public static function getHeaders(): array
    {
        /** @var array<string, string[]> $headers */
        $headers = self::$instance->getHeaders();
        return $headers;
    }

    /**
     * Check if a header exists by the given case-insensitive name.
     * @param string $name
     * @return bool
     */
    public static function hasHeader(string $name): bool
    {
        return self::$instance->hasHeader($name);
    }

    /**
     * Retrieve a header by the given case-insensitive name.
     * @param string $name
     * @return string[]
     */
    public static function getHeader(string $name): array
    {
        return self::$instance->getHeader($name);
    }

    /**
     * Retrieve a comma-separated string of the values for a single header.
     * @param string $name
     * @return string
     */
    public static function getHeaderLine(string $name): string
    {
        return self::$instance->getHeaderLine($name);
    }

    /**
     * Retrieve the body as a stream.
     * @return StreamInterface
     */
    public static function getBody(): StreamInterface
    {
        return self::$instance->getBody();
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     * @param int|HttpStatus $code
     * @param ?string $reasonPhrase
     * @return ResponseInterface
     */
    public static function withStatus(
        int|HttpStatus $code,
        ?string $reasonPhrase = null
    ): ResponseInterface {
        $httpStatus = is_int($code)
            ? HttpStatus::tryFrom($code)
            : $code;

        if (isset($httpStatus)) {
            $code = $httpStatus->getCode();
            $reasonPhrase ??= $httpStatus->getReason();
        }

        /** @var int $code */
        return self::$instance->withStatus($code, $reasonPhrase ?? '');
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     * @param string $name
     * @param string|string[] $value
     * @return ResponseInterface
     */
    public static function withHeader(string $name, $value): ResponseInterface
    {
        return self::$instance->withHeader($name, $value);
    }

    /**
     * Return an instance with the specified header appended with the given value.
     * @param string $name
     * @param string|string[] $value
     * @return ResponseInterface
     */
    public static function withAddedHeader(string $name, $value): ResponseInterface
    {
        return self::$instance->withAddedHeader($name, $value);
    }

    /**
     * Return an instance without the specified header.
     * @param string $name
     * @return ResponseInterface
     */
    public static function withoutHeader(string $name): ResponseInterface
    {
        return self::$instance->withoutHeader($name);
    }

    /**
     * Return an instance with the specified message body.
     * @param StreamInterface $body
     * @return ResponseInterface
     */
    public static function withBody(StreamInterface $body): ResponseInterface
    {
        return self::$instance->withBody($body);
    }

    /**
     * Sends the HTTP response to the client, including headers and body.
     *
     * This method will throw a RuntimeException if headers have already been sent.
     *
     * @param ResponseInterface $response The PSR-7 response to send.
     * @throws \RuntimeException If headers have already been sent.
     * @return void
     */
    public static function send(ResponseInterface $response): void
    {
        if (headers_sent()) {
            throw new \RuntimeException('Headers were already sent!');
        }

        header(
            sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ),
            true
        );

        /** @var array<string, string|string[]> $headers */
        $headers = $response->getHeaders();

        foreach (array_keys($headers) as $name) {
            $header = sprintf('%s: %s', $name, $response->getHeaderLine($name));
            header($header, false);
        }

        echo $response->getBody()->getContents();
    }
}
