<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

use Mingalevme\OneSignal\Exception\ClientException;
use Mingalevme\OneSignal\Exception\NetworkException;
use Mingalevme\OneSignal\Exception\RequestException;
use Mingalevme\OneSignal\Exception\ServerException;
use Mingalevme\OneSignal\Exception\ServiceUnavailableException;
use Mingalevme\OneSignal\Exception\TransferException;
use Mingalevme\OneSignal\Exception\UnexpectedResponseFormatException;
use Mingalevme\OneSignal\Notification\NotificationInterface;
use Mingalevme\Tests\OneSignal\Suites\Integration\ClientTest;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as PsrHttpClient;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface as PsrRequestFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface as PsrStreamFactory;
use RuntimeException;
use Throwable;

use function json_decode;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @see ClientFactory
 * @see ClientTest
 */
class Client implements ClientInterface
{
    protected const BASE_URL = 'https://onesignal.com/api/v1';

    protected const APP_ID = 'app_id';

    /** @var non-empty-string */
    protected string $appId;
    /** @var non-empty-string */
    protected string $restAPIKey;

    protected PsrHttpClient $psrHttpClient;
    protected PsrRequestFactory $psrRequestFactory;
    protected PsrStreamFactory $psrStreamFactory;

    /** @var non-empty-string */
    protected string $baseUrl;

    /**
     * @param CreateClientOptions $createClientOptions
     * @param PsrHttpClient $psrHttpClient
     * @param PsrRequestFactory $psrRequestFactory
     * @param PsrStreamFactory $psrStreamFactory
     */
    public function __construct(
        CreateClientOptions $createClientOptions,
        PsrHttpClient $psrHttpClient,
        PsrRequestFactory $psrRequestFactory,
        PsrStreamFactory $psrStreamFactory
    ) {
        $this->appId = $createClientOptions->getAppId();
        $this->restAPIKey = $createClientOptions->getRestAPIKey();
        $this->baseUrl = $createClientOptions->getBaseUrl() ?: self::BASE_URL;
        $this->psrHttpClient = $psrHttpClient;
        $this->psrRequestFactory = $psrRequestFactory;
        $this->psrStreamFactory = $psrStreamFactory;
    }

    public function createNotification(NotificationInterface $notification): CreateNotificationResult
    {
        $data = $notification->toOneSignalData() + [
                self::APP_ID => $this->appId,
            ];

        $url = "$this->baseUrl/notifications";

        $response = $this->makePostRequest($url, $data, $request);

        /** @var array{id?: ?string, recipients?: ?int, errors?: ?string[], external_id?: ?non-empty-string} $responseData */
        $responseData = $this->parseResponse($request, $response);

        /** @var int<0, max>|mixed|null $recipients */
        $recipients = $responseData['recipients'] ?? null;

        if ($recipients === null) {
            $recipients = 0;
        } elseif (!is_int($recipients) || $recipients < 0) {
            throw new UnexpectedResponseFormatException($request, $response, 'Invalid value of recipients');
        }

        /** @var non-empty-string|mixed|null $id */
        $id = $responseData['id'] ?? null;

        if (empty($id)) {
            $id = null;
        }

        if ($recipients > 0 && empty($id)) {
            throw new UnexpectedResponseFormatException($request, $response, 'Missing notification id');
        }

        if (!empty($id) && !is_string($id)) {
            throw new UnexpectedResponseFormatException($request, $response, 'Invalid notification id');
        }

        if (!empty($id) && empty($recipients)) {
            throw new UnexpectedResponseFormatException($request, $response, 'Missing recipients');
        }

        /** @var mixed|null $externalId */
        $externalId = $responseData['external_id'] ?? null;

        if (empty($externalId)) {
            $externalId = null;
        } elseif (!is_string($externalId)) {
            throw new UnexpectedResponseFormatException($request, $response, 'Invalid value of external_id');
        }

        /** @var non-empty-list<non-empty-string>|null|mixed $errors */
        $errors = $responseData['errors'] ?? null;

        if (empty($errors)) {
            $errors = null;
        } elseif (!is_array($errors)) {
            throw new UnexpectedResponseFormatException($request, $response, 'Invalid value of errors');
        }

        /**
         * @psalm-suppress ArgumentTypeCoercion
         * @psalm-suppress MixedArgumentTypeCoercion
         */
        return new CreateNotificationResult($id, $recipients, $externalId, $errors, $request, $response);
    }

    /**
     * @param string $url
     * @param array<string|int, mixed> $payload
     * @param RequestInterface|null $request
     * @return ResponseInterface
     * @noinspection PhpUnused
     * @codeCoverageIgnore
     */
    protected function makeGetRequest(
        string $url,
        array $payload,
        ?RequestInterface &$request = null
    ): ResponseInterface {
        if ($payload) {
            $url = $this->strContains($url, '?')
                ? $url . '&' . http_build_query($payload)
                : $url . '?' . http_build_query($payload);
        }

        $request = $this->psrRequestFactory->createRequest('GET', $url);

        return $this->makeRequest($request);
    }

    /**
     * @param string $url
     * @param array<string|int, mixed> $payload
     * @param RequestInterface|null $request
     * @param-out RequestInterface $request
     * @return ResponseInterface
     */
    protected function makePostRequest(
        string $url,
        array $payload,
        ?RequestInterface &$request = null
    ): ResponseInterface {
        $request = $this->psrRequestFactory->createRequest('POST', $url);

        if ($payload) {
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                /** @codeCoverageIgnore */
                throw new RuntimeException('Error while encoding payload to json');
            }
            $body = $this->psrStreamFactory->createStream($json);
            $request = $request
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withBody($body);
        }

        return $this->makeRequest($request);
    }

    protected function makeRequest(RequestInterface $request): ResponseInterface
    {
        $request = $request->withHeader('Authorization', "Basic $this->restAPIKey");

        try {
            $response = $this->psrHttpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $message = "Error while sending request to OneSignal: {$e->getMessage()}";
            if ($e instanceof NetworkExceptionInterface) {
                throw new NetworkException($request, $message, null, $e);
            }
            if ($e instanceof RequestExceptionInterface) {
                throw new RequestException($request, null, $message, 0, $e);
            }
            throw new TransferException($request, null, $message, 0, $e);
        }

        return $response;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return array<string, mixed>
     */
    protected function parseResponse(RequestInterface $request, ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 503 || $statusCode === 0) {
            throw new ServiceUnavailableException($request, $response);
        } elseif ($statusCode >= 500) {
            throw new ServerException($request, $response);
        }

        $responseBody = $response->getBody()->getContents();

        if (!$responseBody) {
            throw new ServerException($request, $response);
        }

        try {
            /** @var array<string, mixed>|mixed|null $responseData */
            $responseData = json_decode($responseBody, true);
        } catch (Throwable $e) { // @phpstan-ignore-line
            $responseData = null;
        }

        if (!is_array($responseData)) {
            throw new ServerException($request, $response, 'Response body is not a valid JSON');
        }

        if (isset($responseData['errors'][0]) && !is_string($responseData['errors'][0])) {
            throw new UnexpectedResponseFormatException($request, $response);
        }

        if ($statusCode >= 400 && isset($responseData['errors'][0])) {
            throw new ClientException($request, $response, $responseData['errors'][0]);
        }

        if ($statusCode >= 400) {
            throw new UnexpectedResponseFormatException($request, $response);
        }

        /** @var array<string, mixed> $responseData */
        return $responseData;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function strContains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }
}
