<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

use Mingalevme\OneSignal\Notification\SmsNotification;

class SmsNotificationTest extends AbstractFeatureTestCase
{
    public function testSmsNotification(): void
    {
        $notification = SmsNotification::createNotification('name-id', 'contents-en')
            ->setSmsFrom('sms-from')
            ->setSmsMediaUrls('sms-media-url');
        $attributes = [
            'name' => 'name-id',
            'contents' => [
                'en' => 'contents-en',
            ],
            'sms_from' => 'sms-from',
            'sms_media_urls' => ['sms-media-url'],
        ];
        self::assertNotificationHasAttributes($attributes, $notification, true);
    }
}
