<?php

namespace Emag\SynchronizedBundle\Tests\Stubs;

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
        return $argument;
    }

    public function doNothing()
    {
        
    }

    public function doSomething($something)
    {
        call_user_func($something);
    }

}
