<?php

declare(strict_types=1);

namespace Mantle\Http\Exceptions;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Mantle\Aspects\Response;
use Mantle\Http\HttpMethod;
use Mantle\Http\HttpStatus;

class MethodNotAllowedException extends \RuntimeException implements HttpException
{
    /**
     * @param string[] $allowedMethods
    */
    public function __construct(private array $allowedMethods)
    {
        $validMethods = array_map(fn (HttpMethod $method): string => $method->value, HttpMethod::cases());
        $invalidMethods = array_diff($allowedMethods, $validMethods);

        if (count($invalidMethods) > 0) {
            throw new LogicException('Invalid http methods: '. implode(', ', $invalidMethods));
        }
    }

    public function getHttpStatus(): HttpStatus
    {
        return HttpStatus::METHOD_NOT_ALLOWED;
    }

    public function getResponse(): ?ResponseInterface
    {
        return Response::withStatus($this->getHttpStatus())
            ->withHeader('Allow', implode(', ', $this->allowedMethods));
    }
}
