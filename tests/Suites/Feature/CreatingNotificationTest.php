<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

use Mingalevme\OneSignal\Client;
use Mingalevme\OneSignal\CreateClientOptions;
use Mingalevme\OneSignal\Exception\ClientException;
use Mingalevme\OneSignal\Exception\NetworkException;
use Mingalevme\OneSignal\Exception\OneSignalException;
use Mingalevme\OneSignal\Exception\RequestException;
use Mingalevme\OneSignal\Exception\ServerException;
use Mingalevme\OneSignal\Exception\ServiceUnavailableException;
use Mingalevme\OneSignal\Exception\TransferException;
use Mingalevme\OneSignal\Exception\UnexpectedResponseFormatException;
use Mingalevme\OneSignal\Notification\PushNotification;
use Mingalevme\Tests\OneSignal\Helpers\StaticResponsePsrHttpClient;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * @see Client::createNotification()
 */
class CreatingNotificationTest extends AbstractFeatureTestCase
{
    protected const APP_ID = 'app-id';
    protected const REST_API_KEY = 'rest-api-key';

    protected ?ClientInterface $psrHttpClient = null;

    protected function getPsrHttpClient(): ClientInterface
    {
        if (!$this->psrHttpClient) {
            throw new RuntimeException('PsrHttpClient is not set');
        }
        return $this->psrHttpClient;
    }

    protected function getStaticResponsePsrHttpClient(): StaticResponsePsrHttpClient
    {
        $psrHttpClient = $this->getPsrHttpClient();
        if (!is_a($psrHttpClient, StaticResponsePsrHttpClient::class)) {
            throw new RuntimeException();
        }
        return $psrHttpClient;
    }

    public function testItShouldSendNotification(): void
    {
        $responseBodyData = [
            'id' => 'foo-bar',
        ];
        $client = $this->setUpClient($responseBodyData);
        $notification = PushNotification::createContentsNotification('contents-en')
            ->setData([
                'foo' => 'bar',
            ])
            ->addFilterTag('tag1', '=', 'value1')
            ->addFilterTag('tag2', '>', '1');
        $result = $client->createNotification($notification);
        //
        self::assertSame('foo-bar', $result->getNotificationId());
        //
        self::assertCount(1, $this->getStaticResponsePsrHttpClient()->getRequests());
        $request = $this->getStaticResponsePsrHttpClient()->getLastRequest();
        self::assertSame('Basic ' . self::REST_API_KEY, $request->getHeaderLine('Authorization'));
        $request->getBody()->rewind();
        $requestBodyData = $this->jsonDecode($request->getBody()->getContents());
        self::assertSame(self::APP_ID, $requestBodyData['app_id'] ?? null);
        self::assertSame([
            'en' => 'contents-en',
        ], $requestBodyData['contents'] ?? null);
        self::assertSame([
            'foo' => 'bar',
        ], $requestBodyData['data'] ?? null);
        self::assertSame([
            [
                'field' => 'tag',
                'relation' => '=',
                'key' => 'tag1',
                'value' => 'value1',
            ],
            [
                'field' => 'tag',
                'relation' => '>',
                'key' => 'tag2',
                'value' => '1',
            ],
        ], $requestBodyData['filters'] ?? null);
    }

    public function testItShouldReturnExternalId(): void
    {
        $responseBodyData = [
            'id' => 'notification-id',
            'external_id' => 'external-id',
        ];
        $client = $this->setUpClient($responseBodyData);
        $notification = PushNotification::createContentsNotification('contents-en')
            ->setIncludedSegments('All')
            ->setExternalId('external-id');
        $result = $client->createNotification($notification);
        self::assertSame('external-id', $result->getExternalId());
    }

    public function testItShouldSendNotificationWithMultipleTitles(): void
    {
        $responseBodyData = [
            'id' => 'foo-bar',
        ];
        $client = $this->setUpClient($responseBodyData);
        $notification = PushNotification::createContentsNotification([
            'en' => 'en-title',
            'es' => 'es-title',
        ])->setIncludedSegments('All');
        $client->createNotification($notification);
        //
        self::assertCount(1, $this->getStaticResponsePsrHttpClient()->getRequests());
        $request = $this->getStaticResponsePsrHttpClient()->getLastRequest();
        $request->getBody()->rewind();
        $requestBodyData = $this->jsonDecode($request->getBody()->getContents());
        self::assertSame([
            'en' => 'en-title',
            'es' => 'es-title',
        ], $requestBodyData['contents'] ?? null);
    }

    public function testItShouldNotSendNotificationIfNoRecipients(): void
    {
        $responseBodyData = [
            'id' => '',
            'errors' => [
                'All included players are not subscribed',
            ],
        ];
        $client = $this->setUpClient($responseBodyData);
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        $result = $client->createNotification($notification);
        self::assertSame(null, $result->getNotificationId());
        self::assertCount(1, $result->getErrors() ?: []);
        self::assertSame('All included players are not subscribed', $result->getErrors()[0] ?? null);
    }

    public function testItShouldSendNotificationIfInvalidInvalidExternalUserIds(): void
    {
        $responseBodyData = [
            'id' => 'notification-id',
            'errors' => [
                'invalid_external_user_ids' => ['786956'],
            ],
        ];
        $client = $this->setUpClient($responseBodyData);
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        $result = $client->createNotification($notification);
        self::assertSame('notification-id', $result->getNotificationId());
        self::assertSame(null, $result->getErrors());
        self::assertSame(['786956'], $result->getInvalidExternalUserIds());
        self::assertSame(null, $result->getInvalidPhoneNumbers());
    }

    public function testItShouldSendNotificationIfInvalidInvalidPhoneNumbers(): void
    {
        $responseBodyData = [
            'id' => 'notification-id',
            'errors' => [
                'invalid_phone_numbers' => ['+15555555555', '+14444444444'],
            ],
        ];
        $client = $this->setUpClient($responseBodyData);
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        $result = $client->createNotification($notification);
        self::assertSame('notification-id', $result->getNotificationId());
        self::assertSame(null, $result->getErrors());
        self::assertSame(null, $result->getInvalidExternalUserIds());
        self::assertSame(['+15555555555', '+14444444444'], $result->getInvalidPhoneNumbers());
    }

    public function testItShouldThrowErrorIfNotificationIdIsMissing(): void
    {
        $responseBodyData = [
            'id' => '',
        ];
        $client = $this->setUpClient($responseBodyData);
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        try {
            $client->createNotification($notification);
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(UnexpectedResponseFormatException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfPsrHttpClientException(): void
    {
        $psrHttpClient = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new class extends RuntimeException implements ClientExceptionInterface {
                };
            }
        };
        $this->psrHttpClient = $psrHttpClient;
        $client = $this->getClientFactory()->create(
            CreateClientOptions::new(self::APP_ID, self::REST_API_KEY),
        );
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        try {
            $client->createNotification($notification);
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(TransferException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfPsrHttpNetworkException(): void
    {
        $psrHttpClient = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new class extends RuntimeException implements NetworkExceptionInterface {
                    public function getRequest(): RequestInterface
                    {
                        throw new RuntimeException('Unimplemented');
                    }
                };
            }
        };
        $this->psrHttpClient = $psrHttpClient;
        $client = $this->getClientFactory()->create(
            CreateClientOptions::new(self::APP_ID, self::REST_API_KEY),
        );
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        try {
            $client->createNotification($notification);
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(NetworkException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfPsrHttpRequestException(): void
    {
        $psrHttpClient = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new class extends RuntimeException implements RequestExceptionInterface {
                    public function getRequest(): RequestInterface
                    {
                        throw new RuntimeException('Unimplemented');
                    }
                };
            }
        };
        $this->psrHttpClient = $psrHttpClient;
        $client = $this->getClientFactory()->create(
            CreateClientOptions::new(self::APP_ID, self::REST_API_KEY),
        );
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        try {
            $client->createNotification($notification);
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(RequestException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfServiceUnavailable(): void
    {
        $responseBodyData = [];
        $client = $this->setUpClient($responseBodyData, 503);
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        try {
            $client->createNotification($notification);
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(ServiceUnavailableException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfServerError(): void
    {
        $responseBodyData = [];
        $client = $this->setUpClient($responseBodyData, 500);
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        try {
            $client->createNotification($notification);
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(ServerException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfEmptyResponseBody(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Response body is empty');
        $responseBodyData = null;
        $client = $this->setUpClient($responseBodyData);
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        $client->createNotification($notification);
    }

    public function testItShouldThrowErrorInCaseOfInvalidJson(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Response body is not a valid JSON');
        $response = $this->getPsrResponseFactory()
            ->createResponse()
            ->withBody($this->getPsrStreamFactory()->createStream('aaa'));
        $psrHttpClient = new StaticResponsePsrHttpClient($response);
        $this->psrHttpClient = $psrHttpClient;
        $client = $this->getClientFactory()->create(
            CreateClientOptions::new(self::APP_ID, self::REST_API_KEY)
                ->withBaseUrl('https://myonesignal.com'),
        );
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        $client->createNotification($notification);
    }

    public function testItShouldThrowErrorInCaseOfInvalidResponseBodyFormat(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Response body is not a valid JSON');
        $responseBodyData = 'aaa aaa';
        $client = $this->setUpClient($responseBodyData);
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        $client->createNotification($notification);
    }

    public function testItShouldThrowErrorInCaseOfInvalidErrorFormat(): void
    {
        $responseBodyData = [
            'errors' => [0],
        ];
        $client = $this->setUpClient($responseBodyData);
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        try {
            $client->createNotification($notification);
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(UnexpectedResponseFormatException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfResponseHasError(): void
    {
        $responseBodyData = [
            'errors' => [
                'TEST',
            ],
        ];
        $client = $this->setUpClient($responseBodyData, 400);
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        try {
            $client->createNotification($notification);
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(ClientException::class, $e);
            self::assertSame('TEST', $e->getMessage());
        }
    }

    public function testItShouldThrowErrorInCaseOfErrorResponseWithNoErrors(): void
    {
        $responseBodyData = [
            'errors' => [],
        ];
        $client = $this->setUpClient($responseBodyData, 400);
        $notification = PushNotification::createContentsNotification('test')
            ->setIncludedSegments('All');
        try {
            $client->createNotification($notification);
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(UnexpectedResponseFormatException::class, $e);
        }
    }

    /**
     * @param mixed|null $responseBodyData
     * @param int $statusCode
     * @return Client
     */
    private function setUpClient($responseBodyData, int $statusCode = 200): Client
    {
        $response = $this->getPsrResponseFactory()
            ->createResponse($statusCode);
        if ($responseBodyData) {
            $body = $this->getPsrStreamFactory()->createStream($this->jsonEncode($responseBodyData));
            $response = $response->withBody($body);
        }
        $psrHttpClient = new StaticResponsePsrHttpClient($response);
        $this->psrHttpClient = $psrHttpClient;
        $client = $this->getClientFactory()->create(
            CreateClientOptions::new(self::APP_ID, self::REST_API_KEY),
        );
        if (!is_a($client, Client::class)) {
            throw new RuntimeException();
        }
        return $client;
    }
}
