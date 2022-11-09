<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

use Mingalevme\OneSignal\Client;
use Mingalevme\OneSignal\CreateNotificationOptions;
use Mingalevme\OneSignal\Exception\AllIncludedPlayersAreNotSubscribed;
use Mingalevme\OneSignal\Exception\OneSignalException;
use Mingalevme\Tests\OneSignal\Helpers\StaticResponsePsrHttpClient;
use RuntimeException;

/**
 * @see Client::createNotification()
 */
class CreatingNotificationTest extends AbstractFeatureTestCase
{
    protected const APP_ID = 'app-id';
    protected const REST_API_KEY = 'rest-api-key';

    protected ?StaticResponsePsrHttpClient $psrHttpClient = null;

    protected function getPsrHttpClient(): StaticResponsePsrHttpClient
    {
        if (!$this->psrHttpClient) {
            throw new RuntimeException('PsrHttpClient is not set');
        }
        return $this->psrHttpClient;
    }

    public function testItShouldSendNotification(): void
    {
        $responseBodyData = [
            'id' => 'foo-bar',
            'recipients' => 1,
        ];
        $client = $this->setUpClient($responseBodyData);
        $result = $client->createNotification('title', [
            'foo' => 'bar',
        ], [
            'tag1' => 'value1',
        ], [
            CreateNotificationOptions::FILTERS => [
                [
                    CreateNotificationOptions::FILTERS_FIELD => CreateNotificationOptions::FILTERS_FIELD_TAG,
                    CreateNotificationOptions::FILTERS_TAG_KEY => 'tag2',
                    CreateNotificationOptions::FILTERS_RELATION => '>',
                    CreateNotificationOptions::FILTERS_VALUE => '1',
                ],
            ],
            CreateNotificationOptions::TAGS => [
                [
                    CreateNotificationOptions::FILTERS_TAG_KEY => 'tag3',
                    CreateNotificationOptions::FILTERS_RELATION => '>=',
                    CreateNotificationOptions::FILTERS_VALUE => '1',
                ],
            ],
        ]);
        //
        self::assertSame('foo-bar', $result->getNotificationId());
        self::assertSame(1, $result->getTotalNumberOfRecipients());
        //
        self::assertCount(1, $this->getPsrHttpClient()->getRequests());
        $request = $this->getPsrHttpClient()->getLastRequest();
        $request->getBody()->rewind();
        self::assertSame("Basic " . self::REST_API_KEY, $request->getHeaderLine('Authorization'));
        $requestBodyData = $this->jsonDecode($request->getBody()->getContents());
        self::assertSame(self::APP_ID, $requestBodyData['app_id'] ?? null);
        self::assertSame([
            'en' => 'title',
        ], $requestBodyData['contents'] ?? null);
        self::assertSame([
            'foo' => 'bar',
        ], $requestBodyData['data'] ?? null);
        self::assertSame([
            [
                'field' => 'tag',
                'key' => 'tag2',
                'relation' => '>',
                'value' => '1',
            ],
            [
                'field' => 'tag',
                'key' => 'tag1',
                'relation' => '=',
                'value' => 'value1',
            ],
            [
                'field' => 'tag',
                'key' => 'tag3',
                'relation' => '>=',
                'value' => '1',
            ],
        ], $requestBodyData['filters'] ?? null);
    }

    public function testItShouldNotSendNotificationIfNoRecepients(): void
    {
        $responseBodyData = [
            'id' => '',
            'recipients' => 0,
            'errors' => [
                'All included players are not subscribed',
            ],
        ];
        $client = $this->setUpClient($responseBodyData);
        try {
            $client->createNotification('title');
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(AllIncludedPlayersAreNotSubscribed::class, $e);
        }
    }

    /**
     * @param array<string, mixed> $responseBodyData
     * @param int $statusCode
     * @return Client
     */
    private function setUpClient(array $responseBodyData, int $statusCode = 200): Client
    {
        $body = $this->getPsrStreamFactory()->createStream($this->jsonEncode($responseBodyData));
        $response = $this->getPsrResponseFactory()
            ->createResponse($statusCode)
            ->withBody($body);
        $psrHttpClient = new StaticResponsePsrHttpClient($response);
        $this->psrHttpClient = $psrHttpClient;
        return $this->getClientFactory()->create(self::APP_ID, self::REST_API_KEY);
    }
}
