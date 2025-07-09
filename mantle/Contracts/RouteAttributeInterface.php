<?php

declare(strict_types=1);

namespace Mantle\Contracts;

use Mantle\Http\HttpMethod;

interface RouteAttributeInterface
{
    public function getMethod(): HttpMethod;
    public function getPath(): string;
    /**
     * @return string[] List of middleware classes
     */
    public function getMiddleware(): array;
}
