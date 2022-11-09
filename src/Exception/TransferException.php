<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class TransferException extends RuntimeException implements OneSignalException, ClientExceptionInterface
{
    protected RequestInterface $request;
    protected ?ResponseInterface $response = null;

    public function __construct(
        RequestInterface $request,
        ?ResponseInterface $response,
        ?string $message = null,
        ?int $code = null,
        Throwable $previous = null
    ) {
        $message = $message ?: '';
        $code = $code ?: 0;
        parent::__construct($message, $code, $previous);
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
