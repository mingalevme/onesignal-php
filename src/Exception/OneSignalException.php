<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

interface OneSignalException extends Throwable
{
    public function getRequest(): RequestInterface;

    public function getResponse(): ?ResponseInterface;
}
