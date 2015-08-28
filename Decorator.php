<?php

namespace Sms\SynchronizedBundle;

use Sms\SynchronizedBundle\Exception\CannotAquireLockException;

class Decorator
{

    /**
     *
     * @var Lock[]
     */
    private $locks = array();
    
    private $lockPrefix;
    
    private $originalService;

    public function __construct($originalService, $lockPrefix)
    {
        $this->originalService = $originalService;
        $this->lockPrefix = $lockPrefix;
    }

    public function addLock(Lock $lock)
    {
        $this->locks[] = $lock;
    }

    public function __call($name, $arguments)
    {
        foreach ($this->locks as $lock) {
            if ($name === $lock->getMethod()) {
                $lockName = $this->getLockName($lock, $arguments);
                if (!$lock->getDriver()->getLock($lockName)) {
                    throw new CannotAquireLockException($lockName);
                }
                $return = call_user_func_array(array($this->originalService, $name), $arguments);
                $lock->getDriver()->releaseLock($lockName);
                return $return;
            }
        }
        return call_user_func_array(array($this->originalService, $name), $arguments);
    }


    private function getLockName(Lock $lock, $arguments)
    {
        $lockName = $this->lockPrefix . '_' . $lock->getMethod();
        if (array_key_exists($lock->getArgumentIndex(), $arguments)) {
            $argumentHash = $this->getHashFromValue($arguments[$lock->getArgumentIndex()]);

            $lockName .= sprintf('_%s_%s', $lock->getArgumentIndex(), $argumentHash);
        }
        return $lockName;
    }

    private function getHashFromValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return md5(serialize($value));
        }

        return $value;
    }

}
