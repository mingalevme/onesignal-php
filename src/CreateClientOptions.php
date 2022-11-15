<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

final class CreateClientOptions
{
    /** @var non-empty-string */
    private string $appId;
    /** @var non-empty-string */
    private string $restAPIKey;
    /** @var non-empty-string|null */
    private ?string $baseUrl = null;

    /**
     * @param non-empty-string $appId
     * @param non-empty-string $restAPIKey
     * @return static
     */
    public static function new(string $appId, string $restAPIKey): self
    {
        return new self($appId, $restAPIKey);
    }

    /**
     * @param non-empty-string $appId
     * @param non-empty-string $restAPIKey
     */
    private function __construct(string $appId, string $restAPIKey)
    {
        $this->appId = $appId;
        $this->restAPIKey = $restAPIKey;
    }

    /**
     * @return non-empty-string
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * @return non-empty-string
     */
    public function getRestAPIKey(): string
    {
        return $this->restAPIKey;
    }

    /**
     * @return string|null
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * @param non-empty-string|null $baseUrl
     * @return static
     */
    public function withBaseUrl(?string $baseUrl): self
    {
        $self = clone $this;
        $self->baseUrl = $baseUrl;
        return $self;
    }
}
