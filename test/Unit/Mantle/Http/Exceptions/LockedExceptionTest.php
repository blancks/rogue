<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Http\Exceptions;

use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Http\Exceptions\LockedException;
use Rogue\Mantle\Http\HttpStatus;

class LockedExceptionTest extends TestCase
{
    public function testImplementsHttpException()
    {
        $exception = new LockedException('Locked');
        $this->assertInstanceOf(\Rogue\Mantle\Http\Exceptions\HttpException::class, $exception);
    }

    public function testGetHttpStatus()
    {
        $exception = new LockedException('Locked');
        $this->assertEquals(HttpStatus::LOCKED, $exception->getHttpStatus());
    }

    public function testGetResponseIsNull()
    {
        $exception = new LockedException('Locked');
        $this->assertNull($exception->getResponse());
    }
}
