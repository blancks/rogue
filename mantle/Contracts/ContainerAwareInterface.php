<?php

declare(strict_types=1);

namespace Mantle\Contracts;

interface ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container): void;
}
