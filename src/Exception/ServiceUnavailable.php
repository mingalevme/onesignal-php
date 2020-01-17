<?php

namespace Mingalevme\OneSignal\Exception;

use Mingalevme\OneSignal\Exception;

class ServiceUnavailable extends Exception
{
    /** @var string */
    protected $responseBody;

    /**
     * @param $responseBody
     */
    public function __construct($responseBody)
    {
        $this->responseBody = $responseBody;
        parent::__construct('Service Unavailable');
    }

    /**
     * @return string
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }
}
