<?php

declare(strict_types=1);

namespace Test\Unit\Mantle\Http\Exceptions;

use PHPUnit\Framework\TestCase;
use Mantle\Http\Exceptions\ForbiddenException;
use Mantle\Http\HttpStatus;

class ForbiddenExceptionTest extends TestCase
{
    public function testImplementsHttpException()
    {
        $exception = new ForbiddenException('Forbidden');
        $this->assertInstanceOf(\Mantle\Http\Exceptions\HttpException::class, $exception);
    }

    public function testGetHttpStatus()
    {
        $exception = new ForbiddenException('Forbidden');
        $this->assertEquals(HttpStatus::FORBIDDEN, $exception->getHttpStatus());
    }

    public function testGetResponseIsNull()
    {
        $exception = new ForbiddenException('Forbidden');
        $this->assertNull($exception->getResponse());
    }
}
