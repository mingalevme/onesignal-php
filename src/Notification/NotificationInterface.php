<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

interface NotificationInterface
{
    /**
     * @return non-empty-array<string, mixed>
     */
    public function toOneSignalData(): array;
}
