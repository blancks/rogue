<?php

declare(strict_types=1);

namespace Mantle\Routing\Attributes;

use Attribute;
use Mantle\Http\HttpMethod;

/**
 * Attribute for defining an unmasked HTTP POST route on a method.
 *
 * Extends UnmaskedRoute to specify the POST HTTP method.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class UPost extends UnmaskedRoute
{
    /**
     * UPost constructor.
     *
     * @param string $path The route path.
     * @param string[] $middleware List of ordered middleware classes.
     */
    public function __construct(
        private string $path,
        private array $middleware = []
    ) {
        parent::__construct(HttpMethod::POST, $this->path, $this->middleware);
    }
}
