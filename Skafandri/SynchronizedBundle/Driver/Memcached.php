<?php

namespace Skafandri\SynchronizedBundle\Driver;

class Memcached extends AbstractDriver
{

    private $memcachedService;

    public function __construct($memcachedService)
    {
        $this->memcachedService = $memcachedService;
    }

    protected function lock($lockId)
    {
        
    }

    protected function unlock($lockId)
    {
        
    }

}
