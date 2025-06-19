<?php

declare(strict_types=1);

namespace Rogue\Mantle\Http\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Rogue\Mantle\Http\HttpStatus;

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
