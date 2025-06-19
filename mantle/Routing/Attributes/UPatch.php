<?php

declare(strict_types=1);

namespace Rogue\Mantle\Routing\Attributes;

use Attribute;
use Rogue\Mantle\Http\HttpMethod;

/**
 * Attribute for defining an unmasked HTTP PATCH route on a method.
 *
 * Extends UnmaskedRoute to specify the PATCH HTTP method.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class UPatch extends UnmaskedRoute
{
    /**
     * UPatch constructor.
     *
     * @param string $path The route path.
     */
    public function __construct(
        private string $path
    ) {
        parent::__construct(HttpMethod::PATCH, $this->path);
    }
}
