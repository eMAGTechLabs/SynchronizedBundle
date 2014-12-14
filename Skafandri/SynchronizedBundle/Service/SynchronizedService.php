<?php

namespace Skafandri\SynchronizedBundle\Service;

use DateTime;
use Skafandri\SynchronizedBundle\Driver\DriverInterface;

class SynchronizedService
{

    private $originalService;

    /**
     *
     * @var DriverInterface
     */
    private $driver;
    private $synchronizedMethod;
    private $action;
    private $argument;
    private $retryDuration;
    private $retryCount;
    private $retryInfinite;

    public function __construct($originalService, DriverInterface $driver, $synchronizedMethod, $action, $argument, $retryDuration, $retryCount)
    {
        $this->originalService = $originalService;
        $this->driver = $driver;
        $this->synchronizedMethod = $synchronizedMethod;
        $this->action = $action;
        $this->argument = $argument;
        $this->retryDuration = $retryDuration;
        $this->retryCount = $retryCount;
        $this->retryInfinite = ($retryCount === -1);
        $this->logDebug(
                sprintf(
                        'Synchronized service instance for %s::%s(%s) [driver:%s][action:%s][retryDuration:%s][retryCount:%s]', get_class($originalService), $synchronizedMethod, $argument, get_class($driver), $action, $retryDuration, $retryCount)
        );
    }

    public function __call($name, $arguments)
    {
        if ($name === $this->synchronizedMethod) {
            $lockName = $this->getLockName($arguments);
            if (!$this->getLock($lockName, $this->retryCount)) {
                return false;
            }
            $return = call_user_func_array(array($this->originalService, $name), $arguments);
            $this->releaseLock($lockName);
            return $return;
        }
        return call_user_func_array(array($this->originalService, $name), $arguments);
    }

    private function getLock($lockName, $retries)
    {
        if (!$this->retryInfinite && $retries < 0) {
            return false;
        }
        $this->logDebug(sprintf('getting lock "%s"', $lockName));
        if (!$this->driver->getLock($lockName)) {
            $this->logDebug(sprintf('cannot get lock "%s"', $lockName));
            usleep($this->retryTimeout);
            return $this->getLock($lockName, $retries - 1);
        }
        $this->logDebug(sprintf('lock aquired "%s"', $lockName));
        return true;
    }

    private function releaseLock($lockName)
    {
        $this->driver->releaseLock($lockName);
        $this->logDebug(sprintf('lock released "%s"', $lockName));
    }

    private function getLockName($arguments)
    {
        $lockName = $this->synchronizedMethod;
        if (array_key_exists($this->argument, $arguments)) {
            $lockName .= sprintf('_%s_%s', $this->argument, $arguments[$this->argument]);
        }
        return $lockName;
    }

    private function logDebug($message)
    {
        $time = new DateTime(date('Y-m-d\TH:i:s') . substr(microtime(), 1, 9));
        echo sprintf("\n<br/>[%s] %s\n<br/>", $time->format('Y-m-d H:i:s.u'), $message);
    }

}
