<?php

declare(strict_types=1);

namespace Mantle\Routing;

use Mantle\Contracts\RouteDiscoveryInterface;
use Mantle\Routing\Attributes\Route;
use Mantle\Routing\Attributes\UnmaskedRoute;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Mantle\Http\HttpMethod;

/**
 * Discovers routes and unmasked routes in PHP classes within specified namespaces and directories.
 *
 * This class implements the RouteDiscoveryInterface and is responsible for scanning
 * directories for PHP files, extracting classes and their methods, and yielding route
 * definitions based on Route and UnmaskedRoute attributes.
 */
final class UnmaskedRouteDiscovery implements RouteDiscoveryInterface
{
    /**
     * Discover routes and unmasked routes in the given namespaces and paths.
     *
     * @param string $rootNamespace The root namespace to scan.
     * @param string $rootPath The root directory path corresponding to the root namespace.
     * @param array<string, string> $namespacePaths An associative array of additional namespace => path pairs.
     * @return Generator<array{
     *  method: HttpMethod,
     *  path: string,
     *  action: string[],
     *  middleware: string[],
     * }> Yields arrays containing route data.
     */
    public function discover(
        string $rootNamespace,
        string $rootPath,
        array $namespacePaths = []
    ): Generator {
        $rootPath = normalizePath($rootPath);
        array_walk($namespacePaths, fn ($path) => normalizePath($path));

        /** @var array<string, string> $allNamespacePaths */
        $allNamespacePaths = [$rootNamespace => $rootPath] + $namespacePaths;

        foreach ($allNamespacePaths as $baseNamespace => $basePath) {
            $fileIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($basePath)
            );

            foreach ($fileIterator as $file) {
                if (
                    !($file instanceof \SplFileInfo) ||
                    $file->isDir() ||
                    $file->getExtension() !== 'php'
                ) {
                    continue;
                }

                $filePath = normalizePath($file->getRealPath());
                $className = $this->classNameFromFile($filePath, $basePath, $baseNamespace);

                if (!$this->classExists($className, $filePath)) {
                    continue;
                }

                // NOTE: phpstan wrongly detects $className as invalid FQCN
                // @phpstan-ignore-next-line
                $reflection = new ReflectionClass($className);

                foreach ($reflection->getMethods() as $method) {
                    if ($baseNamespace === $rootNamespace) {
                        yield from $this->discoverMethodUnmaskedRoutes(
                            $method,
                            $baseNamespace,
                            $basePath,
                            $namespacePaths,
                            $className,
                            $filePath
                        );
                    }

                    yield from $this->discoverMethodRoutes($method, $className);
                }
            }
        }
    }

    private function classExists(string $className, string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        if (!class_exists($className)) {
            require_once $filePath;
            return class_exists($className);
        }

        return true;
    }

    private function classNameFromFile(string $filePath, string $baseDir, string $baseNamespace): string
    {
        $filePathWithoutExtension = substr($filePath, 0, -4);

        $ds = DIRECTORY_SEPARATOR;
        $relativePath = str_replace($ds, '/', $filePathWithoutExtension);
        $baseDir = rtrim(str_replace($ds, '/', $baseDir), '/');

        if (str_contains($relativePath, $baseDir)) {
            $relativePath = substr($relativePath, strlen($baseDir));
        }

        $relativePath = trim($relativePath, '/');
        $relativePath = $relativePath
            ? '\\' . implode('\\', array_map(fn ($segment) => ucwords($segment), explode('/', $relativePath)))
            : '';

        return $baseNamespace . $relativePath;
    }

    /**
     * @param ReflectionMethod $method
     * @param string $baseNamespace
     * @param string $basePath
     * @param array<string, string> $namespacePaths
     * @param string $className
     * @param string $filePath
     * @return Generator<array{
     *  method: HttpMethod,
     *  path: string,
     *  action: string[],
     *  middleware: string[],
     * }>
     */
    private function discoverMethodUnmaskedRoutes(
        ReflectionMethod $method,
        string $baseNamespace,
        string $basePath,
        array $namespacePaths,
        string $className,
        string $filePath
    ): Generator {
        foreach ($method->getAttributes(UnmaskedRoute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $unmaskedRouteAttribute = $attribute->newInstance();

            foreach ($namespacePaths as $alternativeNamespace => $alternativePath) {
                $unmaskedClassName = strtr($className, [$baseNamespace => $alternativeNamespace]);
                $unmaskedClassPath = strtr($filePath, [$basePath => $alternativePath]);

                if (
                    $this->classExists($unmaskedClassName, $unmaskedClassPath) &&
                    method_exists($unmaskedClassName, $method->getName())
                ) {
                    yield $this->routeDataFormat(
                        verb: $unmaskedRouteAttribute->getMethod(),
                        path: $unmaskedRouteAttribute->getPath(),
                        className: $unmaskedClassName,
                        methodName: $method->getName(),
                        middleware: $unmaskedRouteAttribute->getMiddleware()
                    );

                    continue 2;
                }
            }

            yield $this->routeDataFormat(
                verb: $unmaskedRouteAttribute->getMethod(),
                path: $unmaskedRouteAttribute->getPath(),
                className: $className,
                methodName: $method->getName(),
                middleware: $unmaskedRouteAttribute->getMiddleware()
            );
        }
    }

    /**
     * @param ReflectionMethod $method
     * @param string $className
     * @return Generator<array{
     *  method: HttpMethod,
     *  path: string,
     *  action: string[],
     *  middleware: string[],
     * }>
     */
    private function discoverMethodRoutes(ReflectionMethod $method, string $className): Generator
    {
        foreach ($method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $routeAttribute = $attribute->newInstance();

            yield $this->routeDataFormat(
                verb: $routeAttribute->getMethod(),
                path: $routeAttribute->getPath(),
                className: $className,
                methodName: $method->getName(),
                middleware: $routeAttribute->getMiddleware()
            );
        }
    }

    /**
     * @param HttpMethod $verb
     * @param string $path
     * @param string $className
     * @param string $methodName
     * @param string[] $middleware
     * @return array{
     *  method: HttpMethod,
     *  path: string,
     *  action: string[],
     *  middleware: string[],
     * }
     */
    private function routeDataFormat(
        HttpMethod $verb,
        string $path,
        string $className,
        string $methodName,
        array $middleware = []
    ): array {
        return [
            'method' => $verb,
            'path' => $path,
            'action' => [$className, $methodName],
            'middleware' => $middleware,
        ];
    }
}
