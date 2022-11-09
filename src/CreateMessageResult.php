<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CreateMessageResult
{
    /** @var non-empty-string */
    private string $notificationId;
    /** @var positive-int */
    private int $count;
    private RequestInterface $request;
    private ResponseInterface $response;

    /**
     * @param non-empty-string $notificationId
     * @param positive-int $count
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(
        string $notificationId,
        int $count,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->notificationId = $notificationId;
        $this->count = $count;
        $this->request = $request;
        $this->response = $response;
    }

    /** @return non-empty-string */
    public function getNotificationId(): string
    {
        return $this->notificationId;
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
