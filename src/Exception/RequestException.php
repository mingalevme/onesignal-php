<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Exception;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class RequestException extends TransferException implements RequestExceptionInterface
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($request, $response, $message, $code, $previous);
    }

    public function getResponse(): ResponseInterface
    {
        $response = $this->response;
        if (!$response) {
            throw new \RuntimeException('Response is not set');
        }
        return $response;
    }
}
