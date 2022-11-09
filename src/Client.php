<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

use InvalidArgumentException;
use Mingalevme\OneSignal\CreateNotificationOptions as CNO;
use Mingalevme\OneSignal\Exception\AllIncludedPlayersAreNotSubscribed;
use Mingalevme\OneSignal\Exception\ClientException;
use Mingalevme\OneSignal\Exception\NetworkException;
use Mingalevme\OneSignal\Exception\RequestException;
use Mingalevme\OneSignal\Exception\ServerException;
use Mingalevme\OneSignal\Exception\ServiceUnavailableException;
use Mingalevme\OneSignal\Exception\TransferException;
use Mingalevme\OneSignal\Exception\UnexpectedResponseFormatException;
use Mingalevme\Tests\OneSignal\Suites\Integration\ClientTest;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as PsrHttpClient;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface as PsrRequestFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface as PsrStreamFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Throwable;

use function json_decode;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @see ClientTest
 */
class Client implements ClientInterface
{
    protected const BASE_URL = 'https://onesignal.com/api/v1';

    protected const APP_ID = 'app_id';

    protected string $appId;
    protected string $restAPIKey;

    protected PsrHttpClient $psrHttpClient;
    protected PsrRequestFactory $psrRequestFactory;
    protected PsrStreamFactory $psrStreamFactory;
    protected LoggerInterface $logger;

    /** @var non-empty-string */
    protected string $defaultSegment = CNO::SEGMENTS_SUBSCRIBED_USERS;

    /** @var non-empty-string */
    protected string $baseUrl = self::BASE_URL;
    private ?bool $debug;

    /**
     *
     * @param non-empty-string $appId
     * @param non-empty-string $restAPIKey
     * @param PsrHttpClient $psrHttpClient
     * @param PsrRequestFactory $psrRequestFactory
     * @param PsrStreamFactory $psrStreamFactory
     * @param LoggerInterface|null $logger
     * @param bool|null $debug
     */
    public function __construct(
        string $appId,
        string $restAPIKey,
        PsrHttpClient $psrHttpClient,
        PsrRequestFactory $psrRequestFactory,
        PsrStreamFactory $psrStreamFactory,
        ?LoggerInterface $logger,
        ?bool $debug = false
    ) {
        $this->appId = $appId;
        $this->restAPIKey = $restAPIKey;
        $this->psrHttpClient = $psrHttpClient;
        $this->psrRequestFactory = $psrRequestFactory;
        $this->psrStreamFactory = $psrStreamFactory;
        $this->logger = $logger ?: new NullLogger();
        $this->debug = $debug;
    }

    /**
     * @param non-empty-string $defaultSegment
     * @return static
     */
    public function setDefaultSegment(string $defaultSegment): self
    {
        $this->defaultSegment = $defaultSegment;
        return $this;
    }

    /**
     * @param non-empty-string $baseUrl
     * @return static
     * @noinspection PhpUnused
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @param bool|null $debug
     * @return $this
     */
    public function setDebug(?bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    public function createNotification(
        $title = null,
        array $payload = null,
        array $whereTags = null,
        array $extra = null
    ): CreateMessageResult {
        $data = [];

        /** @var array<non-empty-string, non-empty-string> $content */
        $content = [];

        if (is_string($title)) {
            $content['en'] = $title;
        } elseif (is_array($title)) {
            $content = $title;
        }

        if (count($content) > 0) {
            $data[CNO::CONTENTS] = $content;
        }

        if ($payload) {
            $data[CNO::DATA] = $payload;
        }
        if ($extra) {
            $data = array_merge($data, $extra);
        }

        /** @var array{
         *     contents?: array<non-empty-string, non-empty-string>|array<string, mixed>,
         *     data?: array,
         *     tags?: array{key: non-empty-string, relation: non-empty-string, value: string}[],
         *     filters?: array<string, mixed>[]
         * }|array<string, mixed> $data
         */

        if (empty($data[CNO::CONTENTS]) && empty($data[CNO::CONTENT_AVAILABLE]) && empty($data[CNO::TEMPLATE_ID])) {
            throw new InvalidArgumentException(
                'Title is required unless content_available=true or template_id is set'
            );
        }

        // English must be included in the hash (https://documentation.onesignal.com/reference/push-channel-properties)
        if (!empty($data[CNO::CONTENTS])) {
            /** @var array<string, mixed> $contents */
            $contents = $data[CNO::CONTENTS];
            /** @psalm-suppress MixedArgument */
            if (empty($contents['en']) || !is_string($contents['en']) || !trim($contents['en'])) {
                throw new InvalidArgumentException('Invalid or missing default text of notification (content["en"])');
            }
        }

        if (empty($data[CNO::FILTERS])) {
            $data[CNO::FILTERS] = [];
        }

        /** @var array<string, array{key: string, relation: string, value: string}> $tags */
        $tags = [];

        foreach ((array)$whereTags as $key => $value) {
            $tags["$key=$value"] = [
                CNO::FILTERS_TAG_KEY => $key,
                CNO::FILTERS_RELATION => '=',
                CNO::FILTERS_VALUE => $value,
            ];
        }

        /** @var array{key: string, relation: string, value: string}[] $tags */
        $tags = array_values($tags);

        if ($data[CNO::TAGS] ?? null) {
            /**
             * @psalm-suppress MixedArgument
             * @phpstan-ignore-next-line
             */
            $tags = array_merge($tags, $data[CNO::TAGS]);
        }

        unset($data[CNO::TAGS]);

        /** @var array{key: string, relation: string, value: string} $tag */
        foreach ($tags as $tag) {
            /**
             * @psalm-suppress MixedArgument
             * @psalm-suppress MixedArrayAssignment
             * @phpstan-ignore-next-line
             */
            $data[CNO::FILTERS][] = [
                    CNO::FILTERS_FIELD => CNO::FILTERS_FIELD_TAG,
                ] + $tag;
        }

        // You must include which players, segments, or tags you wish to send this notification to
        if (
            empty($data[CNO::INCLUDE_PLAYER_IDS])
            && empty($data[CNO::INCLUDED_SEGMENTS])
            && empty($data[CNO::FILTERS])
        ) {
            $data[CNO::INCLUDED_SEGMENTS] = [$this->defaultSegment];
        }

        $data[self::APP_ID] = $this->appId;

        $url = "$this->baseUrl/notifications";

        $response = $this->makePostRequest($url, $data, $request);

        /** @var array{id?: ?string, recipients?: ?int, errors?: ?string[]} $responseData */
        $responseData = $this->parseResponse($request, $response);

        if (isset($responseData['recipients']) && $responseData['recipients'] === 0) {
            throw new AllIncludedPlayersAreNotSubscribed($request, $response);
        }

        /** @var non-empty-string|mixed|null $id */
        $id = $responseData['id'] ?? null;

        if (!is_string($id) || empty($id)) {
            throw new UnexpectedResponseFormatException($request, $response, 'Missing notification id');
        }

        /** @var mixed|int|null $recipients */
        $recipients = $responseData['recipients'] ?? null;

        if (!$recipients || !is_int($recipients) || $recipients < 1) {
            throw new UnexpectedResponseFormatException($request, $response, 'Invalid value of recipients');
        }

        return new CreateMessageResult($id, $recipients, $request, $response);
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
        $context = [
            'app-id' => $this->appId,
            'method' => $request->getMethod(),
            'url' => $request->getUri()->__toString(),
        ];

        if ($this->debug && $request->getBody()->isSeekable()) {
            $context['body'] = $request->getBody()->getContents();
            $request->getBody()->rewind();
        }

        $request = $request->withHeader('Authorization', "Basic $this->restAPIKey");

        $this->logger->debug('Sending request to OneSignal has been started', $context);

        try {
            $response = $this->psrHttpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $message = "Error while sending request to OneSignal: {$e->getMessage()}";
            $this->logger->error($message, $context);
            if ($e instanceof NetworkExceptionInterface) {
                throw new NetworkException($request, $message, null, $e);
            }
            if ($e instanceof RequestExceptionInterface) {
                throw new RequestException($request, null, $message, 0, $e);
            }
            throw new TransferException($request, null, $message, 0, $e);
        }

        $statusCode = $response->getStatusCode();

        $context += [
            'status' => $statusCode,
        ];

        $this->logger->debug('Sending request to OneSignal has been finished', $context);

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
