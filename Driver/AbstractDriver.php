<?php

namespace Sms\SynchronizedBundle\Driver;

abstract class AbstractDriver implements DriverInterface
{

    private $namespace;

    public function getLock($lockName)
    {
        $lockId = $this->getLockId($lockName);
        return $this->lock($lockId);
    }

    public function releaseLock($lockName)
    {
        $lockId = $this->getLockId($lockName);
        $this->unlock($lockId);
    }


    private function getLockId($lockName)
    {
        $lockId = $this->namespace . '_' . $lockName;
        return $this->encodeLockId($lockId);
    }

    protected function encodeLockId($lockname)
    {
        return base64_encode($lockname);
    }

    /**
     * Returns true when a lock is aquired, false otherwise
     * @param string $lockId
     * @return boolean
     */
    abstract protected function lock($lockId);

    /**
     * Releases the $lockId lock if aquired, does nothing otherwise
     * @param string $lockId
     * @return void
     */
    abstract protected function unlock($lockId);
}
