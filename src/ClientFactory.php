<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

use Psr\Http\Client\ClientInterface as PsrHttpClient;
use Psr\Http\Message\RequestFactoryInterface as PsrRequestFactory;
use Psr\Http\Message\StreamFactoryInterface as PsrStreamFactory;

class ClientFactory implements ClientFactoryInterface
{
    private PsrHttpClient $psrHttpClient;
    private PsrRequestFactory $psrRequestFactory;
    private PsrStreamFactory $psrStreamFactory;

    /**
     * @param PsrHttpClient $psrHttpClient
     * @param PsrRequestFactory $psrRequestFactory
     * @param PsrStreamFactory $psrStreamFactory
     */
    public function __construct(
        PsrHttpClient $psrHttpClient,
        PsrRequestFactory $psrRequestFactory,
        PsrStreamFactory $psrStreamFactory
    ) {
        $this->psrHttpClient = $psrHttpClient;
        $this->psrRequestFactory = $psrRequestFactory;
        $this->psrStreamFactory = $psrStreamFactory;
    }

    /**
     * @param CreateClientOptions $createClientOptions
     * @return Client
     */
    public function create(CreateClientOptions $createClientOptions): Client
    {
        return new Client(
            $createClientOptions,
            $this->psrHttpClient,
            $this->psrRequestFactory,
            $this->psrStreamFactory,
        );
    }
}
