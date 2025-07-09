<?php

declare(strict_types=1);

namespace Mantle\Http\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Mantle\Http\HttpStatus;

class UnauthorizedException extends \RuntimeException implements HttpException
{
    public function getHttpStatus(): HttpStatus
    {
        return HttpStatus::UNAUTHORIZED;
    }

    public function getResponse(): ?ResponseInterface
    {
        return null;
    }
}
