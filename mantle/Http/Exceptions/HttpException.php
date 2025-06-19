<?php

declare(strict_types=1);

namespace Rogue\Mantle\Http\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Rogue\Mantle\Http\HttpStatus;

interface HttpException
{
    public function getHttpStatus(): HttpStatus;
    public function getResponse(): ?ResponseInterface;
}
