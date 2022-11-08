<?php

namespace Mingalevme\OneSignal\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ServiceUnavailableException extends ServerException
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ?string $message = null,
        ?int $code = null,
        Throwable $previous = null
    ) {
        $message = $message ?: 'Service Unavailable';
        $code = $code ?: 503;
        parent::__construct($request, $response, $message, $code, $previous);
    }
}
