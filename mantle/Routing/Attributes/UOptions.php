<?php

declare(strict_types=1);

namespace Rogue\Mantle\Routing\Attributes;

use Attribute;
use Rogue\Mantle\Http\HttpMethod;

/**
 * Attribute for defining an unmasked HTTP OPTIONS route on a method.
 *
 * Extends UnmaskedRoute to specify the OPTIONS HTTP method.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class UOptions extends UnmaskedRoute
{
    /**
     * UOptions constructor.
     *
     * @param string $path The route path.
     * @param string[] $middleware List of ordered middleware classes.
     */
    public function __construct(
        private string $path,
        private array $middleware = []
    ) {
        parent::__construct(HttpMethod::OPTIONS, $this->path, $this->middleware);
    }
}
