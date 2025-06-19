<?php

declare(strict_types=1);

namespace Rogue\Mantle\Routing\Attributes;

use Attribute;
use Rogue\Mantle\Http\HttpMethod;

/**
 * Attribute for defining an HTTP HEAD route on a method.
 *
 * Extends Route to specify the HEAD HTTP method.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Head extends Route
{
    /**
     * Head constructor.
     *
     * @param string $path The route path.
     */
    public function __construct(
        private string $path
    ) {
        parent::__construct(HttpMethod::HEAD, $this->path);
    }
}
