<?php

namespace Mingalevme\OneSignal\Exception;

class InvalidPlayerIds extends \Mingalevme\OneSignal\Exception
{
    /**
     * @var array
     */
    protected $ids;

    /**
     * @param array $ids
     */
    public function __construct($ids)
    {
        $this->ids = $ids;
        parent::__construct('Invalid Player Ids');
    }
    
    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
