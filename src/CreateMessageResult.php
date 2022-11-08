<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CreateMessageResult
{
    /** @var non-empty-string */
    private string $id;
    /** @var positive-int */
    private int $count;
    private RequestInterface $request;
    private ResponseInterface $response;

    /**
     * @param non-empty-string $id
     * @param positive-int $count
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(string $id, int $count, RequestInterface $request, ResponseInterface $response)
    {
        $this->id = $id;
        $this->count = $count;
        $this->request = $request;
        $this->response = $response;
    }

    /** @return non-empty-string */
    public function getId(): string
    {
        return $this->id;
    }

    /** @return positive-int */
    public function getTotalNumberOfRecipients(): int
    {
        return $this->count;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
