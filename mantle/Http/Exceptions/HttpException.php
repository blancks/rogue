<?php

declare(strict_types=1);

namespace Mantle\Http\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Mantle\Http\HttpStatus;

interface HttpException
{
    public function getHttpStatus(): HttpStatus;
    public function getResponse(): ?ResponseInterface;
}
