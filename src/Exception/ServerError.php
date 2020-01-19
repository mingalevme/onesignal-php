<?php

namespace Mingalevme\OneSignal\Exception;

use Mingalevme\OneSignal\Exception;

class ServerError extends Exception
{
    /** @var string|null */
    protected $responseBody;

    public function __construct($status = 'Server Error', $responseBody = null, $code = 500, $context = null)
    {
        $status = $status ?: 'Server Error';
        parent::__construct($status, $code, null, $context);
        $this->responseBody = $responseBody;
    }

    /**
     * @return string|null
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }
}
