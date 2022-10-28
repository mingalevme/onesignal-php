<?php

namespace Mingalevme\Tests\OneSignal;

use Dotenv\Dotenv;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use Mingalevme\OneSignal\ClientFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected ?HttpFactory $guzzleHttpFactory = null;

    protected function setUp(): void
    {
        parent::setUp();
        $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../');
        $dotenv->load();
    }

    protected function getStrEnv(string $name, ?string $default = null): ?string
    {
        /** @var string|false $value */
        $value = getenv($name);
        return $value === false
            ? $default
            : $value;
    }

    protected function getClientFactory(): ClientFactory
    {
        return new ClientFactory(
            $this->getPsrHttpClient(),
            $this->getPsrRequestFactory(),
            $this->getPsrStreamFactory(),
            $this->getLogger()
        );
    }

    protected function getPsrHttpClient(): GuzzleHttpClient
    {
        return new GuzzleHttpClient();
    }

    protected function getPsrRequestFactory(): RequestFactoryInterface
    {
        return $this->getGuzzleHttpFactory();
    }

    protected function getPsrStreamFactory(): StreamFactoryInterface
    {
        return $this->getGuzzleHttpFactory();
    }

    protected function getGuzzleHttpFactory(): HttpFactory
    {
        if (!$this->guzzleHttpFactory) {
            $this->guzzleHttpFactory = new HttpFactory();
        }
        return $this->guzzleHttpFactory;
    }

    protected function getLogger(): LoggerInterface
    {
        return new NullLogger();
    }
}
