<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CreateNotificationResult implements CreateNotificationResultInterface
{
    private RequestInterface $request;
    private ResponseInterface $response;

    /** @var non-empty-string|null */
    private ?string $notificationId = null;
    /** @var int<0, max> */
    private int $totalNumberOfRecipients = 0;
    /** @var non-empty-string|null */
    private ?string $externalId = null;

    /** @var non-empty-list<non-empty-string>|null */
    private ?array $errors = null;

    /** @var non-empty-list<non-empty-string>|null */
    private ?array $invalidExternalUserIds = null;

    /** @var non-empty-list<non-empty-string>|null */
    private ?array $invalidPhoneNumbers = null;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    private function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param non-empty-string $notificationId
     * @param int<1, max> $recipients
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return self
     */
    public static function newFromNotificationId(
        string $notificationId,
        int $recipients,
        RequestInterface $request,
        ResponseInterface $response
    ): self {
        $self = new self($request, $response);
        $self->totalNumberOfRecipients = $recipients;
        $self->notificationId = $notificationId;
        return $self;
    }

    /**
     * @param non-empty-list<non-empty-string> $errors
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return self
     */
    public static function newFromErrors(array $errors, RequestInterface $request, ResponseInterface $response): self
    {
        $self = new self($request, $response);
        $self->errors = $errors;
        return $self;
    }

    /** @return non-empty-string|null */
    public function getNotificationId(): ?string
    {
        return $this->notificationId;
    }

    /** @return int<0, max>|null */
    public function getTotalNumberOfRecipients(): ?int
    {
        return $this->totalNumberOfRecipients;
    }

    /**
     * @return non-empty-string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @param non-empty-string $externalId
     * @return $this
     */
    public function setExternalId(string $externalId): self
    {
        $this->externalId = $externalId;
        return $this;
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

    public function getInvalidExternalUserIds(): ?array
    {
        return $this->invalidExternalUserIds;
    }

    /**
     * @param list<non-empty-string>|null $invalidExternalUserIds
     * @return $this
     */
    public function setInvalidExternalUserIds(?array $invalidExternalUserIds): self
    {
        $this->invalidExternalUserIds = !empty($invalidExternalUserIds)
            ? $invalidExternalUserIds
            : null;
        return $this;
    }

    public function getInvalidPhoneNumbers(): ?array
    {
        return $this->invalidPhoneNumbers;
    }

    /**
     * @param list<non-empty-string>|null $invalidPhoneNumbers
     * @return CreateNotificationResult
     */
    public function setInvalidPhoneNumbers(?array $invalidPhoneNumbers): self
    {
        $this->invalidPhoneNumbers = !empty($invalidPhoneNumbers)
            ? $invalidPhoneNumbers
            : null;
        return $this;
    }
}
