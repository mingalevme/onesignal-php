<?php

namespace Mingalevme\Tests\OneSignal\Suites\Integration;

use Mingalevme\OneSignal\Client;
use Mingalevme\OneSignal\CreateNotificationOptions;
use Mingalevme\OneSignal\Exception\AllIncludedPlayersAreNotSubscribed;
use Mingalevme\OneSignal\Exception\ClientException;
use Mingalevme\OneSignal\Exception\OneSignalException;
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
        self::assertNotEmpty($result->getNotificationId());
        self::assertGreaterThan(1, $result->getTotalNumberOfRecipients());
    }

    public function testSendingToActivePlayer(): void
    {
        $playerId = $this->getStrEnv('ONE_SIGNAL_TEST_PLAYER_ID');
        if (empty($playerId)) {
            self::markTestSkipped('Env var ONE_SIGNAL_TEST_PLAYER_ID is not set');
        }
        $result = $this->getClient()->createNotification('test', null, null, [
            CreateNotificationOptions::INCLUDE_PLAYER_IDS => [
                $playerId,
            ],
        ]);
        self::assertNotEmpty($result->getNotificationId());
        self::assertSame(1, $result->getTotalNumberOfRecipients());
    }

    public function testInvalidPlayerIdFormat(): void
    {
        try {
            $this->getClient()->createNotification('test', null, null, [
                CreateNotificationOptions::INCLUDE_PLAYER_IDS => [
                    'invalid-player-id',
                ],
            ]);
            self::fail('Exception has not been thrown');
        } catch (ClientException $e) {
            self::assertSame(
                'Incorrect player_id format in include_player_ids (not a valid UUID): invalid-player-id',
                $e->getMessage()
            );
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

    public function testInvalidCredentials(): void
    {
        $client = $this->getClientFactory()->create(
            '67415017-24e2-4a6c-afc9-a14e7958c4db',
            'Njk4YTE1YWUtZjhlMi00Yzk2LWExZjAtNTg5ZDhiZGRmZGUx'
        );
        try {
            $client->createNotification('test');
            self::fail('Exception has not been thrown');
        } catch (OneSignalException $e) {
            self::assertInstanceOf(ClientException::class, $e);
        }
    }
}
