<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

class TargetingTest extends AbstractFeatureTestCase
{
    public function testTargetingSegments(): void
    {
        $notification = $this->createNotification()
            ->setIncludedSegments('included-segment')
            ->setExcludedSegments('excluded-segments');
        $data = $notification->toOneSignalData();
        self::assertSame(['included-segment'], $data['included_segments']);
        self::assertSame(['excluded-segments'], $data['excluded_segments']);
    }

    public function testTargetingFilters(): void
    {
        $notification = $this->createNotification()
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
        /** @var array{filters: non-empty-list<array>} $data */
        $data = $notification->toOneSignalData();
        self::assertSame([
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
        ], $data['filters']);
    }

    public function testFilerOrClause(): void
    {
        $notification = $this->createNotification()
            ->addFilterLastSession('>', 1)
            ->addFilterOrClause()
            ->addFilterFirstSession('<', 2);
        /** @var array{filters: non-empty-list<array>} $data */
        $data = $notification->toOneSignalData();
        self::assertSame([
            [
                'field' => 'last_session',
                'relation' => '>',
                'hours_ago' => '1',
            ],
            [
                'operator' => 'OR',
            ],
            [
                'field' => 'first_session',
                'relation' => '<',
                'hours_ago' => '2',
            ],
        ], $data['filters']);
    }

    public function testTargetingSpecificDevices(): void
    {
        $notification = $this->createNotification()
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
        self::assertSame(['1dd608f2-c6a1-11e3-851d-000c2940e62c-1'], $data['include_player_ids']);
        self::assertSame(['1dd608f2-c6a1-11e3-851d-000c2940e62c-2'], $data['include_external_user_ids']);
        self::assertSame(['nick@catfac.ts'], $data['include_email_tokens']);
        self::assertSame(['+1999999999'], $data['include_phone_numbers']);
        self::assertSame(['ce777617da7f548fe7a9ab6febb56cf39fba6d38203...'], $data['include_ios_tokens']);
        self::assertSame(['http://s.notify.live.net/u/1/bn1/HmQAAACPaLDr-...'], $data['include_wp_wns_uris']);
        self::assertSame(['amzn1.adm-registration.v1.XpvSSUk0Rc3hTVVV...'], $data['include_amazon_reg_ids']);
        self::assertSame(['APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_...-1'], $data['include_chrome_reg_ids']);
        self::assertSame(['APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_...-2'], $data['include_chrome_web_reg_ids']);
        self::assertSame(['APA91bEeiUeSukAAUdnw3O2RB45FWlSpgJ7Ji_...-3',], $data['include_android_reg_ids']);
    }
}
