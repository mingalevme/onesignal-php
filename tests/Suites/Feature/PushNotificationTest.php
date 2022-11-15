<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

use Mingalevme\OneSignal\Notification\ActionButton;
use Mingalevme\OneSignal\Notification\PushNotification;
use Mingalevme\OneSignal\Notification\WebActionButton;

class PushNotificationTest extends AbstractFeatureTestCase
{
    public function testCreatingContentsNotification(): void
    {
        $notification = PushNotification::createContentsNotification('contents-en');
        $data = $notification->toOneSignalData();
        self::assertSame([
            'contents' => ['en' => 'contents-en'],
            'included_segments' => ['All'],
        ], $data);
    }

    public function testCreatingContentAvailableNotification(): void
    {
        $notification = PushNotification::createContentAvailableNotification([
            'foo' => 'bar',
        ]);
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
            'data' =>
                [
                    'foo' => 'bar',
                ],
            'included_segments' => ['All'],
        ], $data);
    }

    public function testCreatingTemplateNotification(): void
    {
        $notification = PushNotification::createTemplateNotification('be4a8044-bbd6-11e4-a581-000c2940e62c');
        $data = $notification->toOneSignalData();
        self::assertSame([
            'template_id' => 'be4a8044-bbd6-11e4-a581-000c2940e62c',
            'included_segments' => ['All'],
        ], $data);
    }

    public function testPushNotificationContent(): void
    {
        $notification = PushNotification::createContentsNotification('contents-en')
            ->setHeadings('headings-en')
            ->setSubtitle('subtitle-en')
            ->setMutableContent()
            ->setTargetContentIdentifier('target-content-identifier');
        $attributes = [
            'contents' => ['en' => 'contents-en'],
            'headings' => ['en' => 'headings-en'],
            'subtitle' => ['en' => 'subtitle-en'],
            'mutable_content' => true,
            'target_content_identifier' => 'target-content-identifier',
        ];
        self::assertNotificationHasAttributes($attributes, $notification);
    }

    public function testAttachments(): void
    {
        $notification = $this->createPushNotification()
            ->setData([
                'foo1' => 'bar1',
                'foo2' => 'bar2',
            ])
            ->setHuaweiMsgType('data')
            ->setUrl('https://onesignal.com')
            ->setWebUrl('https://onesignal.com/web')
            ->setAppUrl('https://onesignal.com/app')
            ->setIosAttachments([
                'id1' => 'value1',
            ])
            ->setBigPicture('https://onesignal.com/img')
            ->setHuaweiBigPicture('https://onesignal.com/img/huawei')
            ->setChromeWebImage('https://onesignal.com/img/chrome')
            ->setAdmBigPicture('https://onesignal.com/img/adm')
            ->setChromeBigPicture('https://onesignal.com/img/chrome/big');
        $attributes = [
            'data' =>
                [
                    'foo1' => 'bar1',
                    'foo2' => 'bar2',
                ],
            'huawei_msg_type' => 'data',
            'url' => 'https://onesignal.com',
            'web_url' => 'https://onesignal.com/web',
            'app_url' => 'https://onesignal.com/app',
            'ios_attachments' =>
                [
                    'id1' => 'value1',
                ],
            'big_picture' => 'https://onesignal.com/img',
            'huawei_big_picture' => 'https://onesignal.com/img/huawei',
            'chrome_web_image' => 'https://onesignal.com/img/chrome',
            'adm_big_picture' => 'https://onesignal.com/img/adm',
            'chrome_big_picture' => 'https://onesignal.com/img/chrome/big',
        ];
        self::assertNotificationHasAttributes($attributes, $notification);
    }

    public function testPushChannelPropertiesActionButtons(): void
    {
        $notification = $this->createPushNotification()
            ->setButtons([
                new ActionButton('id1', 'Text1', 'icon1'),
                new ActionButton('id2', 'Text2', 'icon2'),
            ])
            ->setWebButtons([
                new WebActionButton('id1', 'Text1', 'icon1', 'launch-url'),
                new WebActionButton('id2', 'Text2', 'icon2'),
            ])
            ->setIosCategory('calendar')
            ->setIconType('custom');
        $attributes = [
            'buttons' =>
                [
                    [
                        'id' => 'id1',
                        'text' => 'Text1',
                        'icon' => 'icon1',
                    ],
                    [
                        'id' => 'id2',
                        'text' => 'Text2',
                        'icon' => 'icon2',
                    ],
                ],
            'web_buttons' =>
                [
                    [
                        'id' => 'id1',
                        'text' => 'Text1',
                        'icon' => 'icon1',
                        'url' => 'launch-url',
                    ],
                    [
                        'id' => 'id2',
                        'text' => 'Text2',
                        'icon' => 'icon2',
                        'url' => 'do_not_open',
                    ],
                ],
            'ios_category' => 'calendar',
            'icon_type' => 'custom',
        ];
        self::assertNotificationHasAttributes($attributes, $notification);
    }

    public function testPushChannelPropertiesActionAppearance(): void
    {
        $notification = $this->createPushNotification()
            ->setAndroidChannelId('123e4567-e89b-12d3-a456-426655440000')
            ->setHuaweiChannelId('123e4567-e89b-12d3-a456-426655440000-huawei')
            ->setExistingAndroidChannelId('existing-android-channel-id')
            ->setHuaweiExistingChannelId('existing-android-channel-id-huawei')
            ->setSmallIcon('small-icon')
            ->setHuaweiSmallIcon('small-icon-huawei')
            ->setLargeIcon('large-icon')
            ->setHuaweiLargeIcon('large-icon-huawei')
            ->setAdmSmallIcon('small-icon-adm')
            ->setAdmLargeIcon('large-icon-adm')
            ->setChromeWebIcon('chrome-web-icon')
            ->setChromeWebBadge('chrome-web-badge')
            ->setFirefoxIcon('firefox-web-badge')
            ->setChromeIcon('chrome-icon')
            ->setIosSound('ios-sound')
            ->setAndroidAccentColor('FFFF0001')
            ->setHuaweiAccentColor('FFFF0002')
            ->setIosBadgeType('SetTo')
            ->setIosBadgeCount(99)
            ->setApnsAlert([
                'loc-key' => 'GAME_PLAY_REQUEST_FORMAT',
                'loc-args' => ["Jenna", "Frank"],
            ]);
        $attributes = [
            'android_channel_id' => '123e4567-e89b-12d3-a456-426655440000',
            'huawei_channel_id' => '123e4567-e89b-12d3-a456-426655440000-huawei',
            'existing_android_channel_id' => 'existing-android-channel-id',
            'huawei_existing_channel_id' => 'existing-android-channel-id-huawei',
            'small_icon' => 'small-icon',
            'huawei_small_icon' => 'small-icon-huawei',
            'large_icon' => 'large-icon',
            'huawei_large_icon' => 'large-icon-huawei',
            'adm_small_icon' => 'small-icon-adm',
            'adm_large_icon' => 'large-icon-adm',
            'chrome_web_icon' => 'chrome-web-icon',
            'chrome_web_badge' => 'chrome-web-badge',
            'firefox_icon' => 'firefox-web-badge',
            'chrome_icon' => 'chrome-icon',
            'ios_sound' => 'ios-sound',
            'android_accent_color' => 'FFFF0001',
            'huawei_accent_color' => 'FFFF0002',
            'ios_badgeType' => 'SetTo',
            'ios_badgeCount' => 99,
            'apns_alert' =>
                [
                    'loc-key' => 'GAME_PLAY_REQUEST_FORMAT',
                    'loc-args' =>
                        [
                            'Jenna',
                            'Frank',
                        ],
                ],
        ];
        self::assertNotificationHasAttributes($attributes, $notification);
    }

    public function testPushChannelPropertiesGroupingAndCollapsing(): void
    {
        $notification = $this->createPushNotification()
            ->setAndroidGroup('android-group')
            ->setAndroidGroupMessage('android-group-message')
            ->setAdmGroup('amd-group')
            ->setAdmGroupMessage('amd-group-message')
            ->setCollapseId('collapse-id')
            ->setWebPushTopic('web-push-topic')
            ->setThreadId('thread-id')
            ->setSummaryArg('summary-arg')
            ->setSummaryArgCount(2)
            ->setIosRelevanceScore(0.5)
            ->setIosInterruptionLevel('active');
        $attributes = [
            'android_group' => 'android-group',
            'android_group_message' =>
                [
                    'en' => 'android-group-message',
                ],
            'adm_group' => 'amd-group',
            'adm_group_message' =>
                [
                    'en' => 'amd-group-message',
                ],
            'collapse_id' => 'collapse-id',
            'web_push_topic' => 'web-push-topic',
            'thread_id' => 'thread-id',
            'summary_arg' => 'summary-arg',
            'summary_arg_count' => 2,
            'ios_relevance_score' => 0.5,
            'ios_interruption_level' => 'active',
        ];
        self::assertNotificationHasAttributes($attributes, $notification);
    }

    public function testPushChannelPropertiesDelivery(): void
    {
        $notification = $this->createPushNotification()
            ->setTtl(3600)
            ->setPriority(1)
            ->setApnsPushTypeOverride('voip')
            ->setEnableFrequencyCap(true);
        $attributes = [
            'ttl' => 3600,
            'priority' => 1,
            'apns_push_type_override' => 'voip',
            'enable_frequency_cap' => true,
        ];
        self::assertNotificationHasAttributes($attributes, $notification);
    }

    private function createPushNotification(): PushNotification
    {
        return PushNotification::createContentsNotification('test');
    }
}
