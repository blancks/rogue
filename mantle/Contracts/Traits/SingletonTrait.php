<?php

declare(strict_types=1);

namespace Rogue\Mantle\Contracts\Traits;

use Error;

trait SingletonTrait
{
    /**
     * Prevent instantiation of classes.
     */
    private function __construct()
    {
    }

    /**
     * Prevent cloning of classes.
     */
    public function __clone()
    {
        throw new Error('Cloning is not allowed for singleton classes.');
    }

    /**
     * Prevent unserialization of classes.
     */
    public function __wakeup()
    {
    }
}
