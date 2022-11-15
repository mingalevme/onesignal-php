<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CreateNotificationResult
{
    /** @var non-empty-string|null */
    private ?string $notificationId;
    /** @var int<0, max> */
    private int $count;
    private RequestInterface $request;
    private ResponseInterface $response;
    /** @var non-empty-string|null */
    private ?string $externalId;
    /** @var non-empty-list<non-empty-string>|null */
    private ?array $errors;

    /**
     * @param non-empty-string|null $notificationId
     * @param int<0, max> $count
     * @param non-empty-string|null $externalId
     * @param non-empty-list<non-empty-string>|null $errors
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(
        ?string $notificationId,
        int $count,
        ?string $externalId,
        ?array $errors,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->notificationId = $notificationId;
        $this->count = $count;
        $this->externalId = $externalId;
        $this->errors = $errors;
        $this->request = $request;
        $this->response = $response;
    }

    /** @return non-empty-string|null */
    public function getNotificationId(): ?string
    {
        return $this->notificationId;
    }

    /** @return int<0, max> */
    public function getTotalNumberOfRecipients(): int
    {
        return $this->count;
    }

    /**
     * @return non-empty-string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @return non-empty-list<non-empty-string>|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
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
