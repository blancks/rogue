<?php

declare(strict_types=1);

namespace Test\Unit\Mantle\Http\Exceptions;

use PHPUnit\Framework\TestCase;
use Mantle\Http\Exceptions\GoneException;
use Mantle\Http\HttpStatus;

class GoneExceptionTest extends TestCase
{
    public function testImplementsHttpException()
    {
        $exception = new GoneException('Gone');
        $this->assertInstanceOf(\Mantle\Http\Exceptions\HttpException::class, $exception);
    }

    public function testGetHttpStatus()
    {
        $exception = new GoneException('Gone');
        $this->assertEquals(HttpStatus::GONE, $exception->getHttpStatus());
    }

    public function testGetResponseIsNull()
    {
        $exception = new GoneException('Gone');
        $this->assertNull($exception->getResponse());
    }
}
