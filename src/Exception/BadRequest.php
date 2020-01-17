<?php

namespace Mingalevme\OneSignal\Exception;

use Mingalevme\OneSignal\Exception;

class BadRequest extends Exception
{
    /** @var array */
    protected $responseData;

    /**
     * @param array $responseData
     */
    public function __construct($responseData)
    {
        $this->responseData = $responseData;
        $message = isset($responseData['errors'][0])
            ? $responseData['errors'][0]
            : \json_encode($responseData);
        parent::__construct($message);
    }
    
    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->responseData;
    }
}
