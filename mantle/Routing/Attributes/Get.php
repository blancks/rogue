<?php

declare(strict_types=1);

namespace Mantle\Routing\Attributes;

use Attribute;
use Mantle\Http\HttpMethod;

/**
 * Attribute for defining an HTTP GET route on a method.
 *
 * Extends Route to specify the GET HTTP method.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Get extends Route
{
    /**
     * Get constructor.
     *
     * @param string $path The route path.
     * @param string[] $middleware List of ordered middleware classes.
     */
    public function __construct(
        private string $path,
        private array $middleware = []
    ) {
        parent::__construct(HttpMethod::GET, $this->path, $this->middleware);
    }
}
