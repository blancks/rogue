<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Http\Exceptions;

use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Http\Exceptions\BadRequestException;
use Rogue\Mantle\Http\HttpStatus;

class BadRequestExceptionTest extends TestCase
{
    public function testImplementsHttpException()
    {
        $exception = new BadRequestException('Bad request');
        $this->assertInstanceOf(\Rogue\Mantle\Http\Exceptions\HttpException::class, $exception);
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
