<?php

namespace Mingalevme\OneSignal;

use InvalidArgumentException;
use Mingalevme\OneSignal\Exception\AllIncludedPlayersAreNotSubscribed;
use Mingalevme\OneSignal\Exception\BadRequest;
use Mingalevme\OneSignal\Exception\InvalidPlayerIds;
use Mingalevme\OneSignal\Exception\ServerError;
use Mingalevme\OneSignal\Exception\ServiceUnavailable;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as PsrHttpClient;
use Psr\Http\Message\RequestFactoryInterface as PsrRequestFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface as PsrStreamFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function json_decode;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class Client implements ClientInterface
{
    protected const BASE_URL = 'https://onesignal.com/api/v1';

    protected const READ_BLOCK_SIZE = 4096;

    protected string $appId;

    protected string $restAPIKey;

    /** @var non-empty-string */
    protected string $defaultSegment = self::SEGMENTS_SUBSCRIBED_USERS;

    protected PsrHttpClient $psrHttpClient;
    protected PsrRequestFactory $psrRequestFactory;
    protected PsrStreamFactory $psrStreamFactory;
    protected LoggerInterface $logger;
    protected string $baseUrl;

    /**
     *
     * @param non-empty-string $appId
     * @param non-empty-string $restAPIKey
     * @param PsrHttpClient $psrHttpClient
     * @param PsrRequestFactory $psrRequestFactory
     * @param PsrStreamFactory $psrStreamFactory
     * @param LoggerInterface|null $logger
     * @param string|null $baseUrl
     */
    public function __construct(
        string $appId,
        string $restAPIKey,
        PsrHttpClient $psrHttpClient,
        PsrRequestFactory $psrRequestFactory,
        PsrStreamFactory $psrStreamFactory,
        ?LoggerInterface $logger,
        ?string $baseUrl = null
    ) {
        $this->appId = $appId;
        $this->restAPIKey = $restAPIKey;
        $this->psrHttpClient = $psrHttpClient;
        $this->psrRequestFactory = $psrRequestFactory;
        $this->psrStreamFactory = $psrStreamFactory;
        $this->logger = $logger ?: new NullLogger();
        $this->baseUrl = $baseUrl ?: self::BASE_URL;
    }

    /**
     * @param non-empty-string $defaultSegment
     */
    public function setDefaultSegment(string $defaultSegment): void
    {
        $this->defaultSegment = $defaultSegment;
    }

    public function createNotification(
        $title = null,
        array $payload = null,
        array $whereTags = null,
        array $extra = null
    ): array {
        $content = [];

        if (is_string($title)) {
            $content['en'] = $title;
        } elseif (is_array($title)) {
            $content = $title;
        }

        $data = [];

        if (count($content) > 0) {
            $data[self::CONTENTS] = $content;
        }

        if ($payload) {
            $data[self::DATA] = $payload;
        }

        if ($extra) {
            $data = array_merge($data, $extra);
        }

        if (!$content && empty($extra[self::CONTENT_AVAILABLE]) && empty($extra[self::TEMPLATE_ID])) {
            throw new InvalidArgumentException(
                'Title is required unless content_available=true or template_id is set'
            );
        }

        // English must be included in the hash (https://documentation.onesignal.com/reference/push-channel-properties)
        if (count($content) > 0 && (empty($content['en']) || !is_string($content['en']) || !trim($content['en']))) {
            throw new InvalidArgumentException('Invalid or missing default text of notification (content["en"])');
        }

        if (empty($data[self::FILTERS])) {
            $data[self::FILTERS] = [];
        }

        $tags = [];

        foreach ((array)$whereTags as $key => $value) {
            $tags["$key=$value"] = [
                'key' => $key,
                'relation' => '=',
                'value' => $value,
            ];
        }

        $tags = array_values($tags);

        if (!empty($data[self::TAGS])) {
            $tags = array_merge($tags, $data[self::TAGS]);
        }

        unset($data[self::TAGS]);

        foreach ($tags as $tag) {
            $data[self::FILTERS][] = [
                    'field' => 'tag',
                ] + $tag;
        }

        // You must include which players, segments, or tags you wish to send this notification to
        if (empty($data[self::INCLUDE_PLAYER_IDS]) && empty($data[self::INCLUDED_SEGMENTS]) && empty($data[self::FILTERS])) {
            $data[self::INCLUDED_SEGMENTS] = [$this->defaultSegment];
        }

        $data[self::APP_ID] = $this->appId;

        $url = self::BASE_URL . '/notifications';

        $response = $this->makePostRequest($url, $data, $request);

        /** @var array{id?: ?string, recipients?: ?int, errors?: ?string[]} $responseData */
        $responseData = $this->parseResponse($request, $response);

        if (isset($responseData['recipients']) && $responseData['recipients'] === 0) {
            throw new AllIncludedPlayersAreNotSubscribed();
        }

        if (isset($responseData['errors']['invalid_player_ids'])) {
            throw new InvalidPlayerIds($responseData['errors']['invalid_player_ids']);
        }

        if (isset($responseData['errors'][0])) {
            throw new Exception($responseData['errors'][0]);
        }

        if (empty($responseData['id'])) {
            throw new ServerError('Missing notification id');
        }

        if (empty($responseData['recipients']) || !is_int($responseData['recipients']) || $responseData['recipients'] < 1) {
            throw new ServerError('Invalid value of recipients', $responseData, 500, $responseData);
        }

        return $responseData;
    }

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

    protected function makePostRequest(
        string $url,
        array $payload,
        ?RequestInterface &$request = null
    ): ResponseInterface {
        $request = $this->psrRequestFactory->createRequest('POST', $url);

        if ($payload) {
            $body = $this->psrStreamFactory->createStream(
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            );
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

        $request = $request->withHeader('Authorization', "Basic $this->restAPIKey");

        $this->logger->debug('Sending request to OneSignal has been started', $context);

        try {
            $response = $this->psrHttpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $this->logger->error("Error while sending request to OneSignal: {$e->getMessage()}", $context);
            throw $e;
        }

        $statusCode = $response->getStatusCode();

        $context += [
            'status' => $statusCode,
        ];

        $this->logger->debug('Sending request to OneSignal has been finished', $context);

        return $response;
    }

    protected function parseResponse(RequestInterface $request, ResponseInterface $response): array
    {
        $context = [
            'app-id' => $this->appId,
            'method' => $request->getMethod(),
            'url' => $request->getUri()->__toString(),
        ];

        $statusCode = $response->getStatusCode();

        $responseBody = $response->getBody()->getContents();

        if (!$responseBody) {
            throw new ServerError(null, $responseBody);
        }

        if ($statusCode === 503) {
            throw new ServiceUnavailable($responseBody, $context);
        } elseif ($statusCode >= 500) {
            throw new ServerError(null, $responseBody, $statusCode, $context);
        }

        try {
            /** @var array|mixed|null $responseData */
            $responseData = json_decode($responseBody, true);
        } catch (\Exception $e) {
            $responseData = null;
        }

        if (!is_array($responseData)) {
            throw new ServerError('Response body is invalid', $responseBody, $statusCode, $context);
        }

        if ($statusCode !== 200) {
            throw new BadRequest($responseData);
        }

        return $responseData;
    }

    protected function strContains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }
}
