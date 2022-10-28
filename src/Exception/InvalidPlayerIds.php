<?php

namespace Mingalevme\OneSignal\Exception;

use Mingalevme\OneSignal\Exception;

class InvalidPlayerIds extends Exception
{
    /** @var string[] */
    protected array $ids;

    /** @param string[] $ids */
    public function __construct($ids)
    {
        $this->ids = $ids;
        parent::__construct('Invalid Player Ids');
    }
    
    /**
     * @return string[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
