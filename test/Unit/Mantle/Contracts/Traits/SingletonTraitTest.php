<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Contracts\Traits;

use Error;
use Exception;
use LogicException;
use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Contracts\Traits\SingletonTrait;

class SingletonTraitTest extends TestCase
{
    public function getTestClassInstance(): object
    {
        return new class () {
            use SingletonTrait;
            public function __construct()
            {
            }
        };
    }

    public function testCloneThrowsException()
    {
        // Arrange
        $instance = $this->getTestClassInstance();

        // Act + Assert
        $this->expectException(Error::class);
        $cloned = clone $instance;
    }

    public function testWakeupThrowsException()
    {
        // Arrange
        $instance = $this->getTestClassInstance();

        // Act + Assert
        $this->expectException(Exception::class);
        unserialize(serialize($instance));
    }

    public function testNewInstanceThrowsException()
    {
        // Act + Assert
        $this->expectException(Error::class);
        new class () {
            use SingletonTrait;
        };
    }
}
