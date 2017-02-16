<?php

namespace Mingalevme\OneSignal\Exception;

class AllIncludedPlayersAreNotSubscribed extends \Mfeed\OneSignal\Exception
{
    public function __construct()
    {
        parent::__construct('All included players are not subscribed');
    }
}
