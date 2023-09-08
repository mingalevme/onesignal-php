<?php

namespace Mingalevme\Tests\OneSignal\Suites\Integration;

use Mingalevme\OneSignal\Client;
use Mingalevme\OneSignal\ClientFactory;
use Mingalevme\OneSignal\CreateClientOptions;
use Mingalevme\OneSignal\CreateNotificationOptions as CNO;
use Mingalevme\OneSignal\Exception\ClientException;
use Mingalevme\OneSignal\Exception\OneSignalException;
use Mingalevme\OneSignal\Notification\PushNotification;
use Mingalevme\Tests\OneSignal\TestCase;
use RuntimeException;

class ClientTest extends TestCase
{
    protected function getClientFactory(): ClientFactory
    {
        $factory = parent::getClientFactory();
        if (!is_a($factory, ClientFactory::class)) {
            throw new RuntimeException();
        }
        return $factory;
    }

    protected function getClient(): Client
    {
        $appId = $this->getStrEnv('ONE_SIGNAL_TEST_APP_ID');
        $restAPIKey = $this->getStrEnv('ONE_SIGNAL_TEST_API_KEY');
        if (empty($appId) || empty($restAPIKey)) {
            self::markTestSkipped('Env vars ONE_SIGNAL_TEST_APP_ID and (or) ONE_SIGNAL_TEST_API_KEY are not set');
        }
        return $this->getClientFactory()->create(CreateClientOptions::new($appId, $restAPIKey));
    }

    public function testSendingToDefaultSegment(): void
    {
        $segment = $this->getStrEnv('ONE_SIGNAL_TEST_DEFAULT_SEGMENT') ?: CNO::SEGMENTS_ALL;
        $result = $this->getClient()->createNotification(
            PushNotification::createContentsNotification('test')
                ->setIncludedSegments($segment),
        );
        self::assertNotEmpty($result->getNotificationId());
    }

    public function testSendingToActivePlayer(): void
    {
        $playerId = $this->getStrEnv('ONE_SIGNAL_TEST_PLAYER_ID');
        if (empty($playerId)) {
            self::markTestSkipped('Env var ONE_SIGNAL_TEST_PLAYER_ID is not set');
        }
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludePlayerIds($playerId);
        $result = $this->getClient()->createNotification($notification);
        self::assertNotEmpty($result->getNotificationId());
    }

    public function testInvalidPlayerIdFormat(): void
    {
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludePlayerIds('invalid-player-id');
        try {
            $this->getClient()->createNotification($notification);
            self::fail('Exception has not been thrown');
        } catch (ClientException $e) {
            self::assertSame(
                'Incorrect player_id format in include_player_ids (not a valid UUID): invalid-player-id',
                $e->getMessage(),
            );
        }
    }

    public function testAllIncludedPlayersAreNotSubscribed(): void
    {
        $notification = PushNotification::createContentsNotification('test')
            ->addFilterTag('_foo', '=', '_bar');
        $result = $this->getClient()->createNotification($notification);
        self::assertSame(null, $result->getNotificationId());
        self::assertCount(1, $result->getErrors() ?: []);
        self::assertSame('All included players are not subscribed', $result->getErrors()[0] ?? null);
    }

    public function testInvalidCredentials(): void
    {
        $client = $this->getClientFactory()->create(
            CreateClientOptions::new(
                '67415017-24e2-4a6c-afc9-a14e7958c4db',
                'Njk4YTE1YWUtZjhlMi00Yzk2LWExZjAtNTg5ZDhiZGRmZGUx',
            ),
        );
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludePlayerIds(CNO::SEGMENTS_ALL);
        try {
            $client->createNotification($notification);
            self::fail('Exception has not been thrown');
        } catch (OneSignalException $e) {
            self::assertInstanceOf(ClientException::class, $e);
        }
    }
}
