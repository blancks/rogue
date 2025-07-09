<?php

declare(strict_types=1);

namespace Test\Unit\Mantle\Http\Exceptions;

use PHPUnit\Framework\TestCase;
use Mantle\Http\Exceptions\UnauthorizedException;
use Mantle\Http\HttpStatus;

class UnauthorizedExceptionTest extends TestCase
{
    public function testImplementsHttpException()
    {
        $exception = new UnauthorizedException('Unauthorized');
        $this->assertInstanceOf(\Mantle\Http\Exceptions\HttpException::class, $exception);
    }

    public function testGetHttpStatus()
    {
        $exception = new UnauthorizedException('Unauthorized');
        $this->assertEquals(HttpStatus::UNAUTHORIZED, $exception->getHttpStatus());
    }

    public function testGetResponseIsNull()
    {
        $exception = new UnauthorizedException('Unauthorized');
        $this->assertNull($exception->getResponse());
    }
}
