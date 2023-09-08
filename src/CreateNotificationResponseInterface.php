<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface CreateNotificationResponseInterface
{
    /** @return non-empty-string|null */
    public function getNotificationId(): ?string;

    /**
     * @return non-empty-string|null
     */
    public function getExternalId(): ?string;

    /**
     * @return non-empty-list<non-empty-string>|null
     */
    public function getErrors(): ?array;

    /**
     * @return non-empty-list<non-empty-string>|null
     */
    public function getInvalidExternalUserIds(): ?array;

    /**
     * @return non-empty-list<non-empty-string>|null
     */
    public function getInvalidPhoneNumbers(): ?array;

    public function getRequest(): RequestInterface;

    public function getResponse(): ResponseInterface;
}
