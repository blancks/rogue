<?php

declare(strict_types=1);

namespace Mantle\Routing\Attributes;

use Attribute;
use Mantle\Http\HttpMethod;

/**
 * Attribute for defining an HTTP PUT route on a method.
 *
 * Extends Route to specify the PUT HTTP method.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Put extends Route
{
    /**
     * Put constructor.
     *
     * @param string $path The route path.
     * @param string[] $middleware List of ordered middleware classes.
     */
    public function __construct(
        private string $path,
        private array $middleware = []
    ) {
        parent::__construct(HttpMethod::PUT, $this->path, $this->middleware);
    }
}
