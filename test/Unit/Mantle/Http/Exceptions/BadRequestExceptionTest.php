<?php

declare(strict_types=1);

namespace Test\Unit\Mantle\Http\Exceptions;

use PHPUnit\Framework\TestCase;
use Mantle\Http\Exceptions\BadRequestException;
use Mantle\Http\HttpStatus;

class BadRequestExceptionTest extends TestCase
{
    public function testImplementsHttpException()
    {
        $exception = new BadRequestException('Bad request');
        $this->assertInstanceOf(\Mantle\Http\Exceptions\HttpException::class, $exception);
    }

    public function testGetHttpStatus()
    {
        $exception = new BadRequestException('Bad request');
        $this->assertEquals(HttpStatus::BAD_REQUEST, $exception->getHttpStatus());
    }

    public function testGetResponseIsNull()
    {
        $exception = new BadRequestException('Bad request');
        $this->assertNull($exception->getResponse());
    }
}
