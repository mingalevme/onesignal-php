<?php

use PHPUnit\Framework\TestCase;
use Mingalevme\OneSignal\Client;
use Mingalevme\OneSignal\Exception\AllIncludedPlayersAreNotSubscribed;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    protected static $client;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $appId = getenv('ONESGINAL_TEST_APP_ID');
        $apiKey = getenv('ONESGINAL_TEST_API_KEY');

        if (empty($appId)) {
            self::fail('ONESGINAL_TEST_APP_ID environment variable required');
        }

        if (empty($apiKey)) {
            self::fail('ONESGINAL_TEST_API_KEY environment variable required');
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

    public function testSend()
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
}
