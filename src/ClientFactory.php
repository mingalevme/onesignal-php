<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

use Psr\Http\Client\ClientInterface as PsrHttpClient;
use Psr\Http\Message\RequestFactoryInterface as PsrRequestFactory;
use Psr\Http\Message\StreamFactoryInterface as PsrStreamFactory;
use Psr\Log\LoggerInterface;

class ClientFactory
{
    private PsrHttpClient $psrHttpClient;
    private PsrRequestFactory $psrRequestFactory;
    private PsrStreamFactory $psrStreamFactory;
    private ?LoggerInterface $logger;
    private ?string $baseUrl;

    public function __construct(
        PsrHttpClient $psrHttpClient,
        PsrRequestFactory $psrRequestFactory,
        PsrStreamFactory $psrStreamFactory,
        ?LoggerInterface $logger,
        ?string $baseUrl = null
    ) {
        $this->psrHttpClient = $psrHttpClient;
        $this->psrRequestFactory = $psrRequestFactory;
        $this->psrStreamFactory = $psrStreamFactory;
        $this->logger = $logger;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param non-empty-string $appId
     * @param non-empty-string $restAPIKey
     * @return Client
     */
    public function create(string $appId, string $restAPIKey): Client
    {
        return new Client(
            $appId,
            $restAPIKey,
            $this->psrHttpClient,
            $this->psrRequestFactory,
            $this->psrStreamFactory,
            $this->logger
        );
    }
}
