<?php

namespace Mingalevme\Tests\OneSignal;

use Mingalevme\OneSignal\Client;
use Mingalevme\OneSignal\Exception\AllIncludedPlayersAreNotSubscribed;

class ClientTest extends TestCase
{
    /** @var Client */
    protected static $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $appId = getenv('ONESIGNAL_TEST_APP_ID');
        $apiKey = getenv('ONESIGNAL_TEST_API_KEY');

        if (empty($appId)) {
            self::fail('ONESIGNAL_TEST_APP_ID environment variable is required');
        }

        if (empty($apiKey)) {
            self::fail('ONESIGNAL_TEST_API_KEY environment variable is required');
        }

        self::$client = new Client($appId, $apiKey);
    }

    public function testExport()
    {
        if (getenv('ONESIGNAL_TEST_DISABLE_EXPORTING')) {
            self::markTestSkipped('Export testing is disabled');
        }
        $url = self::$client->export();
        self::assertTrue(is_string(filter_var($url, \FILTER_VALIDATE_URL)));
    }

    public function testGetAllPlayersViaExport()
    {
        if (getenv('ONESIGNAL_TEST_DISABLE_EXPORTING')) {
            self::markTestSkipped('Export testing is disabled');
        }
        $players = self::$client->getAllPlayersViaExport();

        self::assertTrue(is_array($players));
        self::assertGreaterThan(0, count($players));
    }

    public function testGetAllPlayersViaPlayers()
    {
        $players = self::$client->getAllPlayersViaPlayers();

        self::assertTrue(is_array($players));
        self::assertGreaterThan(0, count($players));
    }

    public function testSendWithoutAnyFilters()
    {
        try {
            $result = self::$client->send('(Mingalevme\OneSignal) PHPUnit Test Message, sorry if you are reading me :)', null, null, [
                Client::INCLUDED_SEGMENTS => ['All'],
                Client::TTL => 1,
            ]);
        } catch (AllIncludedPlayersAreNotSubscribed $e) {
            return;
        }
        
        self::assertArrayHasKey('id', $result);
    }

    public function testSendWithAppVersionFilter()
    {
        try {
            $result = self::$client->send('(Mingalevme\OneSignal) PHPUnit Test Message, sorry if you are reading me :)', null, null, [
                Client::TTL => 1,
                Client::FILTERS => [
                     [
                         Client::FILTERS_FIELD => Client::FILTERS_APP_VERSION,
                         Client::FILTERS_RELATION => '>',
                         Client::FILTERS_VALUE => '1',
                    ],
                ],
            ]);
        } catch (AllIncludedPlayersAreNotSubscribed $e) {
            return;
        }

        self::assertArrayHasKey('id', $result);
    }

    public function testSendWithWhereTag()
    {
        try {
            $result = self::$client->send('(Mingalevme\OneSignal) PHPUnit Test Message, sorry if you are reading this :)', null, [
                'test1' => '1',
            ], [
                Client::TTL => 1,
                Client::TAGS => [
                    [
                        Client::TAGS_KEY => 'test2',
                        Client::TAGS_RELATION => '=',
                        Client::TAGS_VALUE => '2',
                    ],
                ],
                Client::FILTERS => [
                    [
                        Client::FILTERS_FIELD => Client::FILTERS_TAG,
                        Client::FILTERS_TAG_KEY => 'test3',
                        Client::FILTERS_RELATION => '=',
                        Client::FILTERS_VALUE => '3',
                    ],
                ],
            ]);
        } catch (AllIncludedPlayersAreNotSubscribed $e) {
            self::assertTrue(true);
            return;
        }

        self::assertArrayHasKey('id', $result);
    }
}
