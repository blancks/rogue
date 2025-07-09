<?php

declare(strict_types=1);

namespace Mantle\Http\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Mantle\Http\HttpStatus;

class ConflictException extends \RuntimeException implements HttpException
{
    public function getHttpStatus(): HttpStatus
    {
        return HttpStatus::CONFLICT;
    }

    public function getResponse(): ?ResponseInterface
    {
        return null;
    }
}
