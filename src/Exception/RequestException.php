<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Exception;

use Psr\Http\Client\RequestExceptionInterface;

class RequestException extends TransferException implements RequestExceptionInterface
{
}
