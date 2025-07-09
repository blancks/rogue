<?php

declare(strict_types=1);

namespace Mantle\Routing\Handlers;

use Closure;
use JsonException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mantle\Http\HttpStatus;
use Mantle\Http\Psr7\BasicStringStream;

final class FinalRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private Closure $closure,
        private ResponseInterface $response
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = ($this->closure)();

        if (!($response instanceof ResponseInterface)) {
            $response = $this->serializedResponse(HttpStatus::OK, $response);
        }

        return $response;
    }

    /**
     * Serializes the given data to a JSON PSR-7 response.
     *
     * @param HttpStatus $httpStatus The http status of the response
     * @param mixed $data The data to serialize as JSON in the response body.
     * @return ResponseInterface The PSR-7 response with JSON body and appropriate headers.
     * @throws \LogicException If the data cannot be JSON-encoded.
     */
    private function serializedResponse(HttpStatus $httpStatus, mixed $data = null): ResponseInterface
    {
        try {

            $response = $this->response
                ->withStatus($httpStatus->getCode(), $httpStatus->getReason())
                ->withHeader('Content-type', 'application/json;charset=utf-8');

            if (!is_null($data)) {
                $body = json_encode($data, JSON_THROW_ON_ERROR);
                $response = $response->withBody(new BasicStringStream($body));
            }

            return $response;

        } catch (JsonException $e) {

            throw new LogicException(
                'The return type of a controller must be either an instance of PSR-7 ResponseInterface '
                . 'or a JSON-serializable value.',
                previous: $e
            );

        }
    }
}
