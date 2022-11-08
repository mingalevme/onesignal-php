<?php

declare(strict_types=1);

namespace Mingalevme\OneSignal\Exception;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class NetworkException extends TransferException implements NetworkExceptionInterface
{
    public function __construct(
        RequestInterface $request,
        ?string $message = null,
        ?int $code = null,
        Throwable $previous = null
    ) {
        parent::__construct($request, null, $message, $code, $previous);
        $this->request = $request;
    }
}
