<?php

use PHPUnit\Framework\TestCase;
use Mingalevme\OneSignal\Client;

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
        $users = self::$client->export();

        $this->assertTrue(is_array($users));
        $this->assertTrue(count($users) > 0);

        foreach ($users as $user) {
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('identifier', $user);
            $this->assertArrayHasKey('language', $user);
            $this->assertArrayHasKey('timezone', $user);
            $this->assertArrayHasKey('tags', $user);
            $this->assertArrayHasKey('created_at', $user);
        }

        $this->assertTrue(true);
    }


    public function testSend()
    {
        try {
            $result = self::$client->send('(Mingalevme\OneSignal) PHPUnit Test Message, sorry if you saw this :)', null, null, [
                Client::INCLUDED_SEGMENTS => ['All'],
            ]);
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'All included players are not subscribed') {
                throw $e;
            } else {
                return;
            }
        }
        
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('recipients', $result);
    }
}