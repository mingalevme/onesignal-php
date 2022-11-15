<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

use DateTime;
use Mingalevme\OneSignal\CreateNotificationOptions as CNO;

class BodyParamsTest extends AbstractFeatureTestCase
{
    public function testBodyParams(): void
    {
        $notification = $this->createNotification()
            ->setName('internal-campaign-name')
            ->setExternalId('external-id')
            ->setSendAfter(new DateTime('2000-01-01T00:00:00+00:00'))
            ->setDelayedOption(CNO::DELAYED_OPTION_TIMEZONE)
            ->setDeliveryTimeOfDay('21:45')
            ->setThrottleRatePerMinute(1);
        $attributes = [
            'name' => 'internal-campaign-name',
            'external_id' => 'external-id',
            'send_after' => '2000-01-01 00:00:00 GMT+0000',
            'delayed_option' => 'timezone',
            'delivery_time_of_day' => '21:45',
            'throttle_rate_per_minute' => 1,
        ];
        self::assertNotificationHasAttributes($attributes, $notification);
    }
}
