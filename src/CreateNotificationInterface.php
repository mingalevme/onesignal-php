<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

use Mingalevme\OneSignal\Exception\OneSignalException;

interface CreateNotificationInterface
{
    /**
     * @param non-empty-string|array<non-empty-string, non-empty-string>|null $title
     * @param array<string, mixed>|null $payload
     * @param array<string, int|string>|null $whereTags
     * @param array<string, mixed>|null $extra
     * @return CreateMessageResult
     * @throws OneSignalException
     */
    public function createNotification(
        $title = null,
        array $payload = null,
        array $whereTags = null,
        array $extra = null
    ): CreateMessageResult;
}
