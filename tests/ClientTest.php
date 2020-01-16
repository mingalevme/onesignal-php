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
        $url = self::$client->export();
        $this->assertTrue(is_string(filter_var($url, \FILTER_VALIDATE_URL)));
    }

    public function testGetAllPlayersViaExport()
    {
        $players = self::$client->getAllPlayersViaExport();

        $this->assertTrue(is_array($players));
        $this->assertTrue(count($players) > 0);
    }

    public function testGetAllPlayersViaPlayers()
    {
        $players = self::$client->getAllPlayersViaPlayers();

        $this->assertTrue(is_array($players));
        $this->assertTrue(count($players) > 0);
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
        
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('recipients', $result);
    }

    public function testSendWithAppVersionFilter()
    {
        try {
            $result = self::$client->send('(Mingalevme\OneSignal) PHPUnit Test Message, sorry if you are reading me :)', null, null, [
                Client::INCLUDED_SEGMENTS => ['All'],
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

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('recipients', $result);
    }

    public function testSendWithWhereTag()
    {
        try {
            $result = self::$client->send('(Mingalevme\OneSignal) PHPUnit Test Message, sorry if you are reading me :)', null, [
                'test1' => '1',
            ], [
                Client::INCLUDED_SEGMENTS => ['All'],
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
            return;
        }

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('recipients', $result);
    }
}
