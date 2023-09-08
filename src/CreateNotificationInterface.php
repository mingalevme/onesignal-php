<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

use Mingalevme\OneSignal\Exception\OneSignalException;
use Mingalevme\OneSignal\Notification\NotificationInterface;

interface CreateNotificationInterface
{
    /**
     * @param NotificationInterface $notification
     * @return CreateNotificationResponse
     * @throws OneSignalException
     */
    public function createNotification(NotificationInterface $notification): CreateNotificationResponseInterface;
}
