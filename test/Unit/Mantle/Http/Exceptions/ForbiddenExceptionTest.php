<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Http\Exceptions;

use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Http\Exceptions\ForbiddenException;
use Rogue\Mantle\Http\HttpStatus;

class ForbiddenExceptionTest extends TestCase
{
    public function testImplementsHttpException()
    {
        $exception = new ForbiddenException('Forbidden');
        $this->assertInstanceOf(\Rogue\Mantle\Http\Exceptions\HttpException::class, $exception);
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
