<?php

declare(strict_types=1);

namespace Rogue\Mantle\Routing\Attributes;

use Attribute;
use Rogue\Mantle\Contracts\RouteAttributeInterface;
use Rogue\Mantle\Http\HttpMethod;

/**
 * Attribute for defining an unmasked route for a specific HTTP method on a method.
 *
 * An UnmaskedRoute allows a route to be resolved first within the `Mask` namespace.
 * However, if a route with a similar path exists in the `App` namespace, the route
 * resolution will prioritize and target the class in the `App` namespace instead.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class UnmaskedRoute implements RouteAttributeInterface
{
    /**
     * UnmaskedRoute constructor.
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
