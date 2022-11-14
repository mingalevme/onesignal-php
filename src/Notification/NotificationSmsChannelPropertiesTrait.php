<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Notification;

use Mingalevme\OneSignal\CreateNotificationOptions as CNO;

/**
 * @mixin Notification
 */
trait NotificationSmsChannelPropertiesTrait
{
    /**
     * Phone Number used to send SMS. Should be a registered Twilio phone number in E.164 format.
     *
     * @param non-empty-string $value
     * @return $this
     */
    public function setSmsFrom(string $value): self
    {
        return $this->setAttribute(CNO::SMS_FROM, $value);
    }

    /**
     * URLs for the media files to be attached to the SMS content.
     *
     * Limit: 10 media urls with a total max size of 5MBs.
     *
     * @param non-empty-string|non-empty-list<non-empty-string> $value
     * @return $this
     */
    public function setSmsMediaUrls($value): self
    {
        return $this->setAttribute(CNO::SMS_MEDIA_URLS, (array)$value);
    }
}
