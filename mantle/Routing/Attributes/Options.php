<?php

declare(strict_types=1);

namespace Mantle\Routing\Attributes;

use Attribute;
use Mantle\Http\HttpMethod;

/**
 * Attribute for defining an HTTP OPTIONS route on a method.
 *
 * Extends Route to specify the OPTIONS HTTP method.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Options extends Route
{
    /**
     * Options constructor.
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
