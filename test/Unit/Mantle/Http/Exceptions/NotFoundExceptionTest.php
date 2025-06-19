<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Http\Exceptions;

use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Http\Exceptions\NotFoundException;
use Rogue\Mantle\Http\HttpStatus;

class NotFoundExceptionTest extends TestCase
{
    public function testImplementsHttpException()
    {
        $exception = new NotFoundException('Not found');
        $this->assertInstanceOf(\Rogue\Mantle\Http\Exceptions\HttpException::class, $exception);
    }

    public function testGetHttpStatus()
    {
        $exception = new NotFoundException('Not found');
        $this->assertEquals(HttpStatus::NOT_FOUND, $exception->getHttpStatus());
    }

    public function testGetResponseIsNull()
    {
        $exception = new NotFoundException('Not found');
        $this->assertNull($exception->getResponse());
    }
}
