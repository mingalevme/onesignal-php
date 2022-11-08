<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Helpers;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class StaticResponsePsrHttpClient implements ClientInterface
{
    private ResponseInterface $response;

    /** @var RequestInterface[] */
    private array $requests = [];

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        return $this->response;
    }

    /**
     * @return RequestInterface[]
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    public function getLastRequest(): RequestInterface
    {
        if (!$this->requests) {
            throw new RuntimeException('No requests');
        }
        return $this->requests[array_key_last($this->requests)];
    }
}
