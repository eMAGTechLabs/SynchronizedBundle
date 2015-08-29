<?php

namespace Sms\SynchronizedBundle;

use Sms\SynchronizedBundle\Event\LockEvent;
use Sms\SynchronizedBundle\Exception\CannotAquireLockException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Decorator
{

    /**
     *
     * @var Lock[]
     */
    private $locks = array();
    private $lockPrefix;
    private $originalService;

    /**
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct($originalService, $lockPrefix, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->originalService = $originalService;
        $this->lockPrefix = $lockPrefix;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function addLock(Lock $lock)
    {
        $this->locks[] = $lock;
    }

    public function __call($name, $arguments)
    {
        foreach ($this->locks as $lock) {
            if ($name === $lock->getMethod()) {
                $this->executeCriticalSection($lock, $name, $arguments);
            }
        }
        return call_user_func_array(array($this->originalService, $name), $arguments);
    }

    private function executeCriticalSection(Lock $lock, $method, $arguments)
    {
        $lockName = $this->getLockName($lock, $arguments);
        $this->dispatchEvent(LockEvent::EVENT_BEFORE_GET_LOCK, $lock);
        if (!$lock->getDriver()->getLock($lockName)) {
            $this->dispatchEvent(LockEvent::EVENT_FAILURE_GET_LOCK, $lock);
            throw new CannotAquireLockException($lockName);
        }
        $this->dispatchEvent(LockEvent::EVENT_SUCCESS_GET_LOCK, $lock);
        $return = call_user_func_array(array($this->originalService, $method), $arguments);
        $this->dispatchEvent(LockEvent::EVENT_BEFORE_RELEASE_LOCK, $lock);
        $lock->getDriver()->releaseLock($lockName);
        $this->dispatchEvent(LockEvent::EVENT_AFTER_RELEASE_LOCK, $lock);
        return $return;
    }

    private function dispatchEvent($name, Lock $lock)
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch($name, new LockEvent($lock, $this->originalService));
        }
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
