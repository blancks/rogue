<?php

declare(strict_types=1);

namespace Test\Unit\Mantle\Http\Exceptions;

use PHPUnit\Framework\TestCase;
use Mantle\Http\Exceptions\TooManyRequestsException;
use Mantle\Http\HttpStatus;

class TooManyRequestsExceptionTest extends TestCase
{
    public function testImplementsHttpException()
    {
        $exception = new TooManyRequestsException('Too many requests');
        $this->assertInstanceOf(\Mantle\Http\Exceptions\HttpException::class, $exception);
    }

    public function testGetHttpStatus()
    {
        $exception = new TooManyRequestsException('Too many requests');
        $this->assertEquals(HttpStatus::TOO_MANY_REQUESTS, $exception->getHttpStatus());
    }

    public function testGetResponseIsNull()
    {
        $exception = new TooManyRequestsException('Too many requests');
        $this->assertNull($exception->getResponse());
    }
}
