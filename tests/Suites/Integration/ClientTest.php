<?php

namespace Mingalevme\Tests\OneSignal\Suites\Integration;

use Mingalevme\OneSignal\Client;
use Mingalevme\OneSignal\ClientInterface;
use Mingalevme\OneSignal\Exception\AllIncludedPlayersAreNotSubscribed;
use Mingalevme\OneSignal\Exception\BadRequest;
use Mingalevme\Tests\OneSignal\TestCase;

class ClientTest extends TestCase
{
    protected function getClient(): Client
    {
        $appId = $this->getStrEnv('ONE_SIGNAL_TEST_APP_ID');
        $restAPIKey = $this->getStrEnv('ONE_SIGNAL_TEST_API_KEY');
        if (empty($appId) || empty($restAPIKey)) {
            self::markTestSkipped('Env vars ONE_SIGNAL_TEST_APP_ID and (or) ONE_SIGNAL_TEST_API_KEY are not set');
        }
        return $this->getClientFactory()->create($appId, $restAPIKey);
    }

    public function testSendingToDefaultSegment(): void
    {
        $segment = $this->getStrEnv('ONE_SIGNAL_TEST_DEFAULT_SEGMENT');
        $client = $this->getClient();
        if ($segment) {
            $client->setDefaultSegment($segment);
        }
        $result = $client->createNotification('test');
        self::assertNotEmpty($result['id']);
        self::assertGreaterThan(1, $result['recipients']);
    }

    public function testSendingToActivePlayer(): void
    {
        $playerId = $this->getStrEnv('ONE_SIGNAL_TEST_PLAYER_ID');
        if (empty($playerId)) {
            self::markTestSkipped('Env var ONE_SIGNAL_TEST_PLAYER_ID is not set');
        }
        $result = $this->getClient()->createNotification('test', null, null, [
            ClientInterface::INCLUDE_PLAYER_IDS => [
                $playerId,
            ],
        ]);
        self::assertNotEmpty($result['id']);
        self::assertSame(1, $result['recipients']);
    }

    public function testIncorrectPlayerIdFormat(): void
    {
        try {
            $this->getClient()->createNotification('test', null, null, [
                ClientInterface::INCLUDE_PLAYER_IDS => [
                    'invalid-player-id',
                ],
            ]);
            self::fail('Exception has not been thrown');
        } catch (BadRequest $e) {
            self::assertTrue(true);
        }
    }

    public function testAllIncludedPlayersAreNotSubscribed(): void
    {
        try {
            $this->getClient()->createNotification('test', null, [
                '_foo' => '_bar',
            ]);
            self::fail('Exception has not been thrown');
        } catch (AllIncludedPlayersAreNotSubscribed $e) {
            self::assertTrue(true);
        }
    }
}
