<?php

declare(strict_types=1);

namespace Rogue\Mantle\Contracts\Traits;

use Rogue\Mantle\Contracts\ContainerInterface;

trait ContainerAwareTrait
{
    private ContainerInterface $container;

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
