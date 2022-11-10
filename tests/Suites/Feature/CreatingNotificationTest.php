<?php

declare(strict_types=1);

namespace Mingalevme\Tests\OneSignal\Suites\Feature;

use InvalidArgumentException;
use Mingalevme\OneSignal\Client;
use Mingalevme\OneSignal\CreateClientOptions;
use Mingalevme\OneSignal\CreateNotificationOptions as CNO;
use Mingalevme\OneSignal\Exception\AllIncludedPlayersAreNotSubscribed;
use Mingalevme\OneSignal\Exception\ClientException;
use Mingalevme\OneSignal\Exception\NetworkException;
use Mingalevme\OneSignal\Exception\OneSignalException;
use Mingalevme\OneSignal\Exception\RequestException;
use Mingalevme\OneSignal\Exception\ServerException;
use Mingalevme\OneSignal\Exception\ServiceUnavailableException;
use Mingalevme\OneSignal\Exception\TransferException;
use Mingalevme\OneSignal\Exception\UnexpectedResponseFormatException;
use Mingalevme\Tests\OneSignal\Helpers\StaticResponsePsrHttpClient;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

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
            'recipients' => 1,
        ];
        $client = $this->setUpClient($responseBodyData);
        $result = $client->createNotification('title', [
            'foo' => 'bar',
        ], [
            'tag1' => 'value1',
        ], [
            CNO::FILTERS => [
                [
                    CNO::FILTERS_FIELD => CNO::FILTERS_FIELD_TAG,
                    CNO::FILTERS_TAG_KEY => 'tag2',
                    CNO::FILTERS_RELATION => '>',
                    CNO::FILTERS_VALUE => '1',
                ],
            ],
            CNO::TAGS => [
                [
                    CNO::FILTERS_TAG_KEY => 'tag3',
                    CNO::FILTERS_RELATION => '>=',
                    CNO::FILTERS_VALUE => '1',
                ],
            ],
        ]);
        //
        self::assertSame('foo-bar', $result->getNotificationId());
        self::assertSame(1, $result->getTotalNumberOfRecipients());
        //
        self::assertCount(1, $this->getStaticResponsePsrHttpClient()->getRequests());
        $request = $this->getStaticResponsePsrHttpClient()->getLastRequest();
        self::assertSame("Basic " . self::REST_API_KEY, $request->getHeaderLine('Authorization'));
        $request->getBody()->rewind();
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

    public function testItShouldSendNotificationWithMultipleTitles(): void
    {
        $responseBodyData = [
            'id' => 'foo-bar',
            'recipients' => 1,
        ];
        $client = $this->setUpClient($responseBodyData);
        $client->createNotification([
            'en' => 'en-title',
            'es' => 'es-title',
        ]);
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

    public function testItShouldNotSendNotificationIfNoTitleNoContent(): void
    {
        $responseBodyData = [];
        $client = $this->setUpClient($responseBodyData);
        try {
            $client->createNotification();
            $this->exceptionHasNotBeenThrown();
        } catch (Throwable $e) {
            self::assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }

    public function testItShouldNotSendNotificationIfNoRecipients(): void
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

    public function testItShouldThrowErrorIfNotificationIdIsMissing(): void
    {
        $responseBodyData = [
            'id' => '',
            'recipients' => 1,
        ];
        $client = $this->setUpClient($responseBodyData);
        try {
            $client->createNotification('title');
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(UnexpectedResponseFormatException::class, $e);
        }
    }

    public function testItShouldThrowErrorIfRecipientsIsMissing(): void
    {
        $responseBodyData = [
            'id' => 'id',
        ];
        $client = $this->setUpClient($responseBodyData);
        try {
            $client->createNotification('title');
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
            CreateClientOptions::new(self::APP_ID, self::REST_API_KEY)
        );
        try {
            $client->createNotification('test');
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
            CreateClientOptions::new(self::APP_ID, self::REST_API_KEY)
        );
        try {
            $client->createNotification('test');
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
            CreateClientOptions::new(self::APP_ID, self::REST_API_KEY)
        );
        try {
            $client->createNotification('test');
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(RequestException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfServiceUnavailable(): void
    {
        $responseBodyData = [];
        $client = $this->setUpClient($responseBodyData, 503);
        try {
            $client->createNotification('title');
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(ServiceUnavailableException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfServerError(): void
    {
        $responseBodyData = [];
        $client = $this->setUpClient($responseBodyData, 500);
        try {
            $client->createNotification('title');
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(ServerException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfEmptyResponseBody(): void
    {
        $responseBodyData = null;
        $client = $this->setUpClient($responseBodyData);
        try {
            $client->createNotification('title');
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(ServerException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfInvalidResponseBodyFormat(): void
    {
        $responseBodyData = 'aaa aaa';
        $client = $this->setUpClient($responseBodyData);
        try {
            $client->createNotification('title');
            $this->exceptionHasNotBeenThrown();
        } catch (OneSignalException $e) {
            self::assertInstanceOf(ServerException::class, $e);
        }
    }

    public function testItShouldThrowErrorInCaseOfInvalidErrorFormat(): void
    {
        $responseBodyData = [
            'errors' => [0],
        ];
        $client = $this->setUpClient($responseBodyData);
        try {
            $client->createNotification('title');
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
        try {
            $client->createNotification('title');
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
        try {
            $client->createNotification('title');
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
            CreateClientOptions::new(self::APP_ID, self::REST_API_KEY)
        );
        if (!is_a($client, Client::class)) {
            throw new RuntimeException();
        }
        return $client;
    }
}
