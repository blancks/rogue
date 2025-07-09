<?php

declare(strict_types=1);

namespace Test\Unit\Mantle\Http\Exceptions;

use PHPUnit\Framework\TestCase;
use Mantle\Http\Exceptions\ConflictException;
use Mantle\Http\HttpStatus;

class ConflictExceptionTest extends TestCase
{
    public function testImplementsHttpException()
    {
        $exception = new ConflictException('Conflict');
        $this->assertInstanceOf(\Mantle\Http\Exceptions\HttpException::class, $exception);
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
