<?php

namespace Mingalevme\OneSignal\Exception;

use Mingalevme\OneSignal\Exception;

class AllIncludedPlayersAreNotSubscribed extends Exception
{
    public function __construct()
    {
        parent::__construct('All included players are not subscribed');
    }
}
