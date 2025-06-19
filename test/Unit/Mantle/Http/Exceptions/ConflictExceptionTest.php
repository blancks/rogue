<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Http\Exceptions;

use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Http\Exceptions\ConflictException;
use Rogue\Mantle\Http\HttpStatus;

class ConflictExceptionTest extends TestCase
{
    public function testImplementsHttpException()
    {
        $exception = new ConflictException('Conflict');
        $this->assertInstanceOf(\Rogue\Mantle\Http\Exceptions\HttpException::class, $exception);
    }

    public function testGetHttpStatus()
    {
        $exception = new ConflictException('Conflict');
        $this->assertEquals(HttpStatus::CONFLICT, $exception->getHttpStatus());
    }

    public function testGetResponseIsNull()
    {
        $exception = new ConflictException('Conflict');
        $this->assertNull($exception->getResponse());
    }
}
