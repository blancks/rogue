<?php

declare(strict_types=1);

use Mantle\Aspects\Container;

if (!function_exists('autowire')) {
    /**
     * Resolves and instantiates a class or invokes a closure using the dependency injection container.
     *
     * @param string|\Closure $entity The class name or closure to resolve/invoke.
     * @return mixed The resolved class instance or the result of the closure invocation.
     */
    function autowire(string|\Closure $entity): mixed
    {
        if ($entity instanceof \Closure) {
            return Container::call($entity);
        }

        return Container::make($entity);
    }
}

if (!function_exists('rootPath')) {
    /**
     * Returns the absolute root path of the project.
     *
     * @param string $path relative path to append
     * @return string The absolute path to the project root directory.
     */
    function rootPath(string $path = ''): string
    {
        return dirname(__FILE__, 2) . ($path === '' ? '' : DIRECTORY_SEPARATOR . normalizePath($path));
    }
}

if (!function_exists('storagePath')) {
    /**
     * Returns the absolute path to the cache directory inside the storage folder.
     *
     * @param string $path relative path to append
     * @return string The absolute path to the cache directory.
     */
    function storagePath(string $path = ''): string
    {
        return rootPath(implode(DIRECTORY_SEPARATOR, [
            'storage',
            ...($path !== '' ? [$path] : [])
        ]));
    }
}

if (!function_exists('cachePath')) {
    /**
     * Returns the absolute path to the cache directory inside the storage folder.
     *
     * @param string $path relative path to append
     * @return string The absolute path to the cache directory.
     */
    function cachePath(string $path = ''): string
    {
        return storagePath(implode(DIRECTORY_SEPARATOR, [
            'cache',
            ...($path !== '' ? [$path] : [])
        ]));
    }
}

if (!function_exists('logsPath')) {
    /**
     * Returns the absolute path to the cache directory inside the storage folder.
     *
     * @param string $path relative path to append
     * @return string The absolute path to the cache directory.
     */
    function logsPath(string $path = ''): string
    {
        return storagePath(implode(DIRECTORY_SEPARATOR, [
            'logs',
            ...($path !== '' ? [$path] : [])
        ]));
    }
}

if (!function_exists('tmpPath')) {
    /**
     * Returns the absolute path to the cache directory inside the storage folder.
     *
     * @param string $path relative path to append
     * @return string The absolute path to the cache directory.
     */
    function tmpPath(string $path = ''): string
    {
        return storagePath(implode(DIRECTORY_SEPARATOR, [
            'tmp',
            ...($path !== '' ? [$path] : [])
        ]));
    }
}

if (!function_exists('normalizePath')) {
    /**
     * Normalizes a path by converting all forward slashes to the system's directory separator.
     *
     * @param string $path The path to normalize.
     * @return string The normalized path.
     */
    function normalizePath(string $path): string
    {
        return implode(DIRECTORY_SEPARATOR, explode('/', $path));
    }
}
