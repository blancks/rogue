<?php

declare(strict_types=1);

namespace Rogue\Mantle\Aspects;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Rogue\Mantle\Contracts\Traits\SingletonTrait;
use Rogue\Mantle\Http\HttpMethod;

/**
 * Singleton wrapper and utility for PSR-7 ServerRequestInterface.
 *
 * Provides static accessors and helpers for HTTP request data,
 * using a singleton instance of a PSR-7 ServerRequest implementation.
 */
class Request
{
    use SingletonTrait;

    private static ServerRequestInterface $instance;

    /**
     * Set the PSR-7 ServerRequest implementation to use.
     *
     * @param ServerRequestInterface $serverRequest
     * @return void
     */
    public static function setInstance(ServerRequestInterface $serverRequest): void
    {
        self::$instance = $serverRequest;
    }

    /**
     * Get the singleton PSR-7 ServerRequest instance.
     */
    public static function getInstance(): ServerRequestInterface
    {
        return self::$instance;
    }

    /**
     * Retrieve server parameters.
     * @return array $_SERVER-like array of server params.
     * @phpstan-return array<string, mixed>
     */
    public static function getServerParams(): array
    {
        /** @var array<string, mixed> $params */
        $params = self::$instance->getServerParams();
        return $params;
    }

    /**
     * Retrieve cookies.
     * @return array
     * @phpstan-return array<string, string>
     */
    public static function getCookieParams(): array
    {
        /** @var array<string, string> $params */
        $params = self::$instance->getCookieParams();
        return $params;
    }

    /**
     * Retrieve query string arguments.
     * @return array
     * @phpstan-return array<string, string>
     */
    public static function getQueryParams(): array
    {
        /** @var array<string, string> $params */
        $params = self::$instance->getQueryParams();
        return $params;
    }

    /**
     * Retrieve normalized file upload data.
     * @return array
     * @phpstan-return array{
     *  name: string|string[],
     *  type: string|string[],
     *  tmp_name: string|string[],
     *  error: int|int[],
     *  size: int|int[]
     * }
     */
    public static function getUploadedFiles(): array
    {
        /** @var array{name: string|string[], type: string|string[], tmp_name: string|string[], error: int|int[], size: int|int[]} $params */
        $params = self::$instance->getUploadedFiles();
        return $params;
    }

    /**
     * Retrieve any parameters provided in the request body.
     * @return null|array|object
     * @phpstan-return null|array<mixed, mixed>|object
     */
    public static function getParsedBody(): null|array|object
    {
        /** @var null|array<mixed, mixed>|object $params */
        $params = self::$instance->getParsedBody();
        return $params;
    }

    /**
     * Retrieve attributes derived from the request.
     * @return array
     * @phpstan-return array<string, mixed>
     */
    public static function getAttributes(): array
    {
        /** @var array<string, mixed> $params */
        $params = self::$instance->getAttributes();
        return $params;
    }

    /**
     * Retrieve a single derived request attribute.
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getAttribute(string $name, mixed $default = null): mixed
    {
        return self::$instance->getAttribute($name, $default);
    }

    /**
     * Retrieve the request target.
     * @return string
     */
    public static function getRequestTarget(): string
    {
        return self::$instance->getRequestTarget();
    }

    /**
     * Retrieve the HTTP method of the request.
     * @return HttpMethod
     */
    public static function getMethod(): HttpMethod
    {
        return HttpMethod::from(self::$instance->getMethod());
    }

    /**
     * Retrieve the URI instance.
     * @return UriInterface
     */
    public static function getUri(): UriInterface
    {
        return self::$instance->getUri();
    }
}
