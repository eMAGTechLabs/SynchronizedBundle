<?php

namespace Sms\SynchronizedBundle\Tests;

class TestService
{

    public function sleep1()
    {
        $this->sleep(1);
    }
    
    public function sleep2()
    {
        $this->sleep(2);
    }

    public function sleep($seconds, $argument = null)
    {
        sleep($seconds);
    }

}
