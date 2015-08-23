<?php

namespace Sms\SynchronizedBundle;

class Lock
{

    /**
     *
     * @var string
     */
    private $method;

    /**
     *
     * @var string
     */
    private $argumentIndex;

    /**
     *
     * @var Driver\DriverInterface
     */
    private $driver;

    public function getMethod()
    {
        return $this->method;
    }

    public function getArgumentIndex()
    {
        return $this->argumentIndex;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setArgumentIndex($argument)
    {
        $this->argumentIndex = $argument;
        return $this;
    }

    public function setDriver(Driver\DriverInterface $driver)
    {
        $this->driver = $driver;
        return $this;
    }

}
