<?php

namespace Emag\SynchronizedBundle\Tests\Stubs;

class TestLogger implements \Psr\Log\LoggerInterface
{

    private $logs = array();

    public function alert($message, array $context = array())
    {
        
    }

    public function critical($message, array $context = array())
    {
        
    }

    public function debug($message, array $context = array())
    {
        
    }

    public function emergency($message, array $context = array())
    {
        
    }

    public function error($message, array $context = array())
    {
        
    }

    public function info($message, array $context = array())
    {
        
    }

    public function log($level, $message, array $context = array())
    {
        $this->logs[] = array('level' => $level, 'message' => $message, $context = $context);
    }

    public function notice($message, array $context = array())
    {
        
    }

    public function warning($message, array $context = array())
    {
        
    }

    public function getLogs()
    {
        return $this->logs;
    }

}
