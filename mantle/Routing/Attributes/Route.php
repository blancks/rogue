<?php

declare(strict_types=1);

namespace Rogue\Mantle\Routing\Attributes;

use Attribute;
use Rogue\Mantle\Contracts\RouteAttributeInterface;
use Rogue\Mantle\Http\HttpMethod;

/**
 * Attribute for defining a generic HTTP route on a method.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class Route implements RouteAttributeInterface
{
    /**
     * Route constructor.
     *
     * @param HttpMethod $method The HTTP method.
     * @param string $path The route path.
     */
    public function __construct(
        private HttpMethod $method,
        private string $path
    ) {
    }

    /**
     * Get the HTTP method for the route.
     *
     * @return HttpMethod
     */
    public function getMethod(): HttpMethod
    {
        return $this->method;
    }

    /**
     * Get the route path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
