<?php

namespace Sms\SynchronizedBundle\Driver;

class Debug extends AbstractDriver
{
    
    public function __construct()
    {
        $this->clearLocks();
    }

    protected function lock($lockId)
    {
        return true;
    }

    protected function unlock($lockId)
    {
        
    }

    public function clearLocks()
    {
        
    }

}
