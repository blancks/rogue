<?php

declare(strict_types=1);

namespace Mantle\Http\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mantle\Aspects\Response;
use Mantle\Http\Exceptions\HttpException;
use Mantle\Http\HttpStatus;
use Throwable;

/**
 * Middleware to handle exceptions during HTTP request processing.
 *
 * Catches HttpException and generic Throwable, returning appropriate HTTP responses.
 */
final class ExceptionHandlerMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, handling exceptions.
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @param RequestHandlerInterface $handler The request handler to delegate to.
     * @return ResponseInterface The response, with error handling if exceptions occur.
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {

            $response = $handler->handle($request);

        } catch (HttpException $e) {

            $response = $e->getResponse() ?? Response::withStatus($e->getHttpStatus()); // TODO: inject dependency

        } /*catch (Throwable $e) {

            $response = Response::withStatus(HttpStatus::INTERNAL_SERVER_ERROR);

        }*/

        return $response;
    }
}
