<?php

namespace Skafandri\SynchronizedBundle\Service;

use Skafandri\SynchronizedBundle\Driver\DriverInterface;

/**
 * @author skafandri
 */
class SynchronizedService
{

    private $originalService;
    private $synchronizedMethod;

    /**
     *
     * @var DriverInterface
     */
    private $driver;
    private $action;
    private $argument;

    public function __construct($originalService, $synchronizedMethod, $driver, $action, $argument)
    {
        $this->originalService = $originalService;
        $this->synchronizedMethod = $synchronizedMethod;
        $this->driver = $driver;
        $this->action = $action;
        $this->argument = $argument;
        $this->driver->setNameSpace(get_class($this->originalService) . '_' . $this->synchronizedMethod);
    }

    public function __call($name, $arguments)
    {
        if ($name === $this->synchronizedMethod) {
            $lockName = $this->getLockName($arguments);
            if (!$this->getLock($lockName)) {
                return;
            }
        }
        call_user_func_array(array($this->originalService, $name), $arguments);
    }

    private function getLock($lockName, $retries = 10)
    {
        if (!$retries) {
            return false;
        }
        if (!$this->driver->getLock($lockName)) {
            usleep(10000);
            $this->getLock($lockName, $retries - 1);
        }
        return true;
    }

    private function getLockName($arguments)
    {
        if (array_key_exists($this->argument, $arguments)) {
            return base64_encode($arguments[$this->argument]);
        }
        return 'lock';
    }

}
