<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal;

interface ClientFactoryInterface
{
    public function create(CreateClientOptions $createClientOptions): ClientInterface;
}
