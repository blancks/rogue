<?php

declare(strict_types=1);

namespace Rogue\Mantle\Contracts;

use Rogue\Mantle\Http\HttpMethod;

interface RouteDiscoveryInterface
{
    /**
     * Discover routes in the given namespaces and paths.
     *
     * @param string $rootNamespace The root namespace to scan.
     * @param string $rootPath The root directory path corresponding to the root namespace.
     * @param array<string, string> $namespacePaths An associative array of additional namespace => path pairs.
     * @return \Generator<array{
     *  method: HttpMethod,
     *  path: string,
     *  action: string[],
     *  middleware: string[]
     * }> Yields arrays containing route data.
     */
    public function discover(
        string $rootNamespace,
        string $rootPath,
        array $namespacePaths = []
    ): \Generator;
}
