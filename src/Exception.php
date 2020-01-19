<?php

namespace Mingalevme\OneSignal;

use Throwable;

class Exception extends \RuntimeException
{
    /** @var array|null */
    protected $context;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $context = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * @return array|null
     */
    public function getContext()
    {
        return $this->context;
    }
}
