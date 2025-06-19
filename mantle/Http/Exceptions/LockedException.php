<?php

declare(strict_types=1);

namespace Rogue\Mantle\Http\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Rogue\Mantle\Http\HttpStatus;

class LockedException extends \RuntimeException implements HttpException
{
    public function getHttpStatus(): HttpStatus
    {
        return HttpStatus::LOCKED;
    }

    public function getResponse(): ?ResponseInterface
    {
        return null;
    }
}
