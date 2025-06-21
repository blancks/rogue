<?php

declare(strict_types=1);

namespace Rogue\Mantle\Contracts;

interface ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container): void;
}
