<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

class PlatformToDeliverTest extends AbstractFeatureTestCase
{
    public function testPlatformToDeliverTo(): void
    {
        $notification = $this->createNotification()
            ->setIsIos(true)
            ->setIsAndroid(true)
            ->setIsHuawei(true)
            ->setIsAnyWeb(true)
            ->setIsChromeWeb(true)
            ->setIsFirefox(true)
            ->setIsSafari(true)
            ->setIsWpWns(true)
            ->setIsAdm(true)
            ->setIsChrome(true)
            ->setChannelForExternalUserIds('email');
        $attributes = [
            'isIos' => true,
            'isAndroid' => true,
            'isHuawei' => true,
            'isAnyWeb' => true,
            'isChromeWeb' => true,
            'isFirefox' => true,
            'isSafari' => true,
            'isWP_WNS' => true,
            'isAdm' => true,
            'isChrome' => true,
            'channel_for_external_user_ids' => 'email',
        ];
        self::assertNotificationHasAttributes($attributes, $notification);
    }
}
