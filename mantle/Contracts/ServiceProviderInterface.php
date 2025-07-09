<?php

declare(strict_types=1);

namespace Mantle\Contracts;

/**
 * Interface ServiceProviderInterface
 *
 * Defines the contract for service providers that register bindings
 * or perform setup in the dependency injection container.
 */
interface ServiceProviderInterface
{
    /**
     * Register services or bindings in the container.
     *
     * @return void
     */
    public function register(): void;

    /**
     * Perform post-registration bootstrapping actions.
     *
     * This method is called after all service providers have been registered.
     * Use it to perform actions that require other services to be available.
     *
     * @return void
     */
    public function boot(): void;
}
