<?php

declare(strict_types=1);

namespace Mantle\Contracts\Traits;

use Mantle\Contracts\ContainerInterface;

trait ContainerAwareTrait
{
    private ContainerInterface $container;

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
