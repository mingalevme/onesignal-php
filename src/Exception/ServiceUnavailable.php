<?php

namespace Mingalevme\OneSignal\Exception;

class ServiceUnavailable extends ServerError
{
    /**
     * @param string|$responseBody
     * @param array|null $context
     */
    public function __construct($responseBody, $context = null)
    {
        parent::__construct('Service Unavailable', $responseBody, 503, $context);
    }
}
