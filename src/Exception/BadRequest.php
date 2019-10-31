<?php

namespace Mingalevme\OneSignal\Exception;

use Mingalevme\OneSignal\Exception;

class BadRequest extends Exception
{
    /**
     * @var array
     */
    protected $response;

    public function __construct($response)
    {
        $this->response = $response;
        $message = isset($response['errors'][0]) ? $response['errors'][0] : \json_encode($response);
        parent::__construct($message);
    }
    
    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }
}
