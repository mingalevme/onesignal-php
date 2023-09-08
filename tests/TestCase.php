<?php

namespace Mingalevme\Tests\OneSignal;

use Dotenv\Dotenv;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\RequestOptions;
use Mingalevme\OneSignal\ClientFactory;
use Mingalevme\OneSignal\ClientFactoryInterface;
use Psr\Http\Client\ClientInterface as PsrHttpClient;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected ?HttpFactory $guzzleHttpFactory = null;

    protected $backupStaticAttributes = null;
    protected $runTestInSeparateProcess = null;

    protected function setUp(): void
    {
        parent::setUp();
        $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();
    }

    protected function getStrEnv(string $name, ?string $default = null): ?string
    {
        /**
         * @psalm-suppress UnnecessaryVarAnnotation
         * @var string|false $value
         */
        $value = getenv($name);
        return $value === false
            ? $default
            : $value;
    }

    protected function getClientFactory(): ClientFactoryInterface
    {
        return new ClientFactory(
            $this->getPsrHttpClient(),
            $this->getPsrRequestFactory(),
            $this->getPsrStreamFactory(),
        );
    }

    protected function getPsrHttpClient(): PsrHttpClient
    {
        return new GuzzleHttpClient([
            RequestOptions::TIMEOUT => 5.0,
        ]);
    }

    protected function getPsrRequestFactory(): RequestFactoryInterface
    {
        return $this->getGuzzleHttpFactory();
    }

    protected function getPsrResponseFactory(): ResponseFactoryInterface
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

    /**
     * @param mixed $data
     * @return string
     */
    protected function jsonEncode($data): string
    {
        $value = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($value === false) {
            throw new RuntimeException('Error while encoding json');
        }
        return $value;
    }

    /**
     * @param string $json
     * @return array<string, mixed>
     */
    protected function jsonDecode(string $json): array
    {
        /** @var array<string, mixed>|mixed $data */
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new RuntimeException('JSON is not an object');
        }
        /** @var array<string, mixed> $data */
        return $data;
    }

    /**
     * @return never-return
     */
    protected function exceptionHasNotBeenThrown(): void
    {
        self::fail('Exception has not been thrown');
    }
}
