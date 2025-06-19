<?php

declare(strict_types=1);

namespace Rogue\Mantle\Routing\Attributes;

use Attribute;
use Rogue\Mantle\Http\HttpMethod;

/**
 * Attribute for defining an unmasked HTTP PUT route on a method.
 *
 * Extends UnmaskedRoute to specify the PUT HTTP method.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class UPut extends UnmaskedRoute
{
    /**
     * UPut constructor.
     *
     * @param string $path The route path.
     */
    public function __construct(
        private string $path
    ) {
        parent::__construct(HttpMethod::PUT, $this->path);
    }
}
