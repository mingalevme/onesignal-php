<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

use DateTime;
use Mingalevme\OneSignal\CreateNotificationOptions as CNO;
use Mingalevme\OneSignal\Notification\ActionButton;
use Mingalevme\OneSignal\Notification\Notification;
use Mingalevme\OneSignal\Notification\WebActionButton;

/**
 * @see Notification
 */
class NotificationBuildingTest extends AbstractFeatureTestCase
{
    private function createContentAvailableNotification(): Notification
    {
        return (new Notification())->setContentAvailable(true);
    }

    public function testTargetingSegments(): void
    {
        $notification = $this->createContentAvailableNotification()
            ->setIncludedSegments('setIncludedSegments')
            ->setExcludedSegments('setExcludedSegments');
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
            'included_segments' =>
                [
                    'setIncludedSegments',
                ],
            'excluded_segments' =>
                [
                    'setExcludedSegments',
                ],
        ], $data);
    }

    public function testTargetingFilters(): void
    {
        $notification = $this->createContentAvailableNotification()
            ->addFilterLastSession('>', 1)
            ->addFilterFirstSession('<', 2)
            ->addFilterSessionCount('>', 3)
            ->addFilterSessionTime('>', 4)
            ->addFilterAmountSpent('>', '0.9')
            ->addFilterBoughtSku('>', 'com.domain.100coinpack', '0.99')
            ->addFilterTag('tag1', '>', '101')
            ->addFilterTag('tag2', '<', '102')
            ->addFilterTagExists('tag3')
            ->addFilterTagNotExists('tag4')
            ->addFilterLanguageEquals('es')
            ->addFilterLanguageNotEquals('en')
            ->addFilterAppVersion('>', '1.0.0')
            ->addFilterLocation(1000, '-117.773', '37.160')
            ->addFilterEmail('test@example.com')
            ->addFilterCountryEquals('us');
        $data = $notification->toOneSignalData();
        self::assertSame([
            'filters' =>
                [
                    [
                        'field' => 'last_session',
                        'relation' => '>',
                        'hours_ago' => '1',
                    ],
                    [
                        'field' => 'first_session',
                        'relation' => '<',
                        'hours_ago' => '2',
                    ],
                    [
                        'field' => 'session_count',
                        'relation' => '>',
                        'value' => 3,
                    ],
                    [
                        'field' => 'session_time',
                        'relation' => '>',
                        'value' => 4,
                    ],
                    [
                        'field' => 'amount_spent',
                        'relation' => '>',
                        'value' => '0.9',
                    ],
                    [
                        'field' => 'bought_sku',
                        'relation' => '>',
                        'key' => 'com.domain.100coinpack',
                        'value' => '0.99',
                    ],
                    [
                        'field' => 'tag',
                        'relation' => '>',
                        'key' => 'tag1',
                        'value' => '101',
                    ],
                    [
                        'field' => 'tag',
                        'relation' => '<',
                        'key' => 'tag2',
                        'value' => '102',
                    ],
                    [
                        'field' => 'tag',
                        'relation' => 'exists',
                        'value' => 'tag3',
                    ],
                    [
                        'field' => 'tag',
                        'relation' => 'not_exists',
                        'value' => 'tag4',
                    ],
                    [
                        'field' => 'language',
                        'relation' => '=',
                        'value' => 'es',
                    ],
                    [
                        'field' => 'language',
                        'relation' => '!=',
                        'value' => 'en',
                    ],
                    [
                        'field' => 'app_version',
                        'relation' => '>',
                        'value' => '1.0.0',
                    ],
                    [
                        'field' => 'location',
                        'radius' => 1000,
                        'lat' => '-117.773',
                        'long' => '37.160',
                    ],
                    [
                        'field' => 'email',
                        'value' => 'test@example.com',
                    ],
                    [
                        'field' => 'country',
                        'relation' => '=',
                        'value' => 'us',
                    ],
                ],
            'content_available' => true,
        ], $data);
    }

    public function testFilerOrClause(): void
    {
        $notification = (new Notification())
            ->setContentAvailable(true)
            ->addFilterLastSession('>', 1)
            ->addFilterOrClause()
            ->addFilterFirstSession('<', 2);
        /** @var array{filters: array[]} $data */
        $data = $notification->toOneSignalData();
        self::assertSame([
            0 =>
                [
                    'field' => 'last_session',
                    'relation' => '>',
                    'hours_ago' => '1',
                ],
            1 =>
                [
                    'operator' => 'OR',
                ],
            2 =>
                [
                    'field' => 'first_session',
                    'relation' => '<',
                    'hours_ago' => '2',
                ],
        ], $data['filters']);
    }

    public function testTargetingSpecificDevices(): void
    {
        $notification = $this->createContentAvailableNotification()
            ->setIncludePlayerIds('1dd608f2-c6a1-11e3-851d-000c2940e62c-1')
            ->setIncludeExternalUserIds('1dd608f2-c6a1-11e3-851d-000c2940e62c-2')
            ->setIncludeEmailTokens('nick@catfac.ts')
            ->setIncludePhoneNumbers('+1999999999')
            ->setIncludeIosTokens('ce777617da7f548fe7a9ab6febb56cf39fba6d38203...')
            ->setIncludeWpWnsUris('http://s.notify.live.net/u/1/bn1/HmQAAACPaLDr-...')
            ->setIncludeAmazonRegIds('amzn1.adm-registration.v1.XpvSSUk0Rc3hTVVV...')
            ->setIncludeChromeRegIds('APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_...-1')
            ->setIncludeChromeWebRegIds('APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_...-2')
            ->setIncludeAndroidRegIds('APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_...-3');
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
            'include_player_ids' => ['1dd608f2-c6a1-11e3-851d-000c2940e62c-1'],
            'include_external_user_ids' => ['1dd608f2-c6a1-11e3-851d-000c2940e62c-2'],
            'include_email_tokens' => ['nick@catfac.ts'],
            'include_phone_numbers' => ['+1999999999'],
            'include_ios_tokens' => ['ce777617da7f548fe7a9ab6febb56cf39fba6d38203...'],
            'include_wp_wns_uris' => ['http://s.notify.live.net/u/1/bn1/HmQAAACPaLDr-...'],
            'include_amazon_reg_ids' => ['amzn1.adm-registration.v1.XpvSSUk0Rc3hTVVV...'],
            'include_chrome_reg_ids' => ['APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_...-1'],
            'include_chrome_web_reg_ids' => ['APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_...-2'],
            'include_android_reg_ids' => ['APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_...-3',],
        ], $data);
    }

    public function testPlatformToDeliverTo(): void
    {
        $notification = $this->createContentAvailableNotification()
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
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
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
        ], $data);
    }

    public function testBodyParams(): void
    {
        $notification = $this->createContentAvailableNotification()
            ->setTemplateId('be4a8044-bbd6-11e4-a581-000c2940e62c')
            ->setName('INTERNAL_CAMPAIGN_NAME')
            ->setExternalId('external-id')
            ->setSendAfter(new DateTime('2000-01-01T00:00:00+00:00'))
            ->setDelayedOption(CNO::DELAYED_OPTION_TIMEZONE)
            ->setDeliveryTimeOfDay('21:45')
            ->setThrottleRatePerMinute(1);
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
            'template_id' => 'be4a8044-bbd6-11e4-a581-000c2940e62c',
            'name' => 'INTERNAL_CAMPAIGN_NAME',
            'external_id' => 'external-id',
            'send_after' => '2000-01-01 00:00:00 GMT+0000',
            'delayed_option' => 'timezone',
            'delivery_time_of_day' => '21:45',
            'throttle_rate_per_minute' => 1,
        ], $data);
    }

    public function testPushChannelPropertiesPushNotificationContent(): void
    {
        $notification = $this->createContentAvailableNotification()
            ->setTitle('push-title')
            ->setSubtitle('push-subtitle')
            ->setText('push-text')
            ->setContentAvailable(true)
            ->setTargetContentIdentifier('target-content-identifier');
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
            'headings' =>
                [
                    'en' => 'push-title',
                ],
            'subtitle' =>
                [
                    'en' => 'push-subtitle',
                ],
            'contents' =>
                [
                    'en' => 'push-text',
                ],
            'target_content_identifier' => 'target-content-identifier',
        ], $data);
    }

    public function testPushChannelPropertiesAttachments(): void
    {
        $notification = $this->createContentAvailableNotification()
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
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
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
        ], $data);
    }

    public function testPushChannelPropertiesActionButtons(): void
    {
        $notification = $this->createContentAvailableNotification()
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
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
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
        ], $data);
    }

    public function testPushChannelPropertiesActionAppearance(): void
    {
        $notification = $this->createContentAvailableNotification()
            ->setAndroidChannelId('123e4567-e89b-12d3-a456-426655440000')
            ->setHuaweiChannelId('123e4567-e89b-12d3-a456-426655440000-huawei')
            ->setExistingAndroidChannelId('existing-android-channel-id')
            ->setHuaweiExistingChannelId('existing-android-channel-id-huawei')
            ->setAndroidBackgroundLayout([
                'image' => 'image',
                'headings_color' => 'FF0000FF',
                'contents_color' => 'FFFF0000',
            ])
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
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
            'android_channel_id' => '123e4567-e89b-12d3-a456-426655440000',
            'huawei_channel_id' => '123e4567-e89b-12d3-a456-426655440000-huawei',
            'existing_android_channel_id' => 'existing-android-channel-id',
            'huawei_existing_channel_id' => 'existing-android-channel-id-huawei',
            'android_background_layout' =>
                [
                    'image' => 'image',
                    'headings_color' => 'FF0000FF',
                    'contents_color' => 'FFFF0000',
                ],
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
        ], $data);
    }

    public function testPushChannelPropertiesGroupingAndCollapsing(): void
    {
        $notification = $this->createContentAvailableNotification()
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
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
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
        ], $data);
    }

    public function testPushChannelPropertiesDelivery(): void
    {
        $notification = $this->createContentAvailableNotification()
            ->setTtl(3600)
            ->setPriority(1)
            ->setApnsPushTypeOverride('voip')
            ->setEnableFrequencyCap(true);
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
            'ttl' => 3600,
            'priority' => 1,
            'apns_push_type_override' => 'voip',
            'enable_frequency_cap' => true,
        ], $data);
    }

    public function testEmailChannelProperties(): void
    {
        $notification = $this->createContentAvailableNotification()
            ->setEmailSubject('subject')
            ->setEmailBody('body')
            ->setEmailFromName('from')
            ->setEmailFromAddress('from@example.com')
            ->setEmailPreheader('preheader')
            ->setDisableEmailClickTracking(true);
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
            'email_subject' => 'subject',
            'email_body' => 'body',
            'email_from_name' => 'from',
            'email_from_address' => 'from@example.com',
            'email_preheader' => 'preheader',
            'disable_email_click_tracking' => true,
        ], $data);
    }

    public function testSmsChannelProperties(): void
    {
        $notification = $this->createContentAvailableNotification()
            ->setSmsFrom('sms-from')
            ->setSmsMediaUrls('sms-media-url');
        $data = $notification->toOneSignalData();
        self::assertSame([
            'content_available' => true,
            'sms_from' => 'sms-from',
            'sms_media_urls' => ['sms-media-url'],
        ], $data);
    }

    public function testSettingRawValue(): void
    {
        $notification = (new Notification([
            'attr1' => 'value1',
            'attr5' => 'value5',
        ]));
        $notification
            ->setContentAvailable(true)
            ->setAttribute('attr2', 'value2')
            ->setAttributes([
                'attr3' => 'value3',
                'attr4' => 'value4',
                'attr5' => 'value5-1',
            ]);
        self::assertSame([
            'attr1' => 'value1',
            'attr5' => 'value5-1',
            'content_available' => true,
            'attr2' => 'value2',
            'attr3' => 'value3',
            'attr4' => 'value4',
        ], $notification->toOneSignalData());
    }
}
