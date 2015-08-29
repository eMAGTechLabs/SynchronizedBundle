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
    private $originalServiceClass;

    /**
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     *
     * @var \Psr\Log\LoggerInterface;
     */
    private $logger;

    public function __construct($originalService, $lockPrefix)
    {
        $this->originalService = $originalService;
        $this->originalServiceClass = get_class($originalService);
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
                $this->executeCriticalSection($lock, $name, $arguments);
            }
        }
        return call_user_func_array(array($this->originalService, $name), $arguments);
    }

    private function executeCriticalSection(Lock $lock, $method, $arguments)
    {
        $lockName = $this->getLockName($lock, $arguments);
        $this->dispatchAndLogEvent(LockEvent::EVENT_BEFORE_GET_LOCK, $lock, $lockName);
        if (!$lock->getDriver()->getLock($lockName)) {
            $this->dispatchAndLogEvent(LockEvent::EVENT_FAILURE_GET_LOCK, $lock, $lockName);
            throw new CannotAquireLockException($lockName);
        }
        $this->dispatchAndLogEvent(LockEvent::EVENT_SUCCESS_GET_LOCK, $lock, $lockName);
        $return = call_user_func_array(array($this->originalService, $method), $arguments);
        $this->dispatchAndLogEvent(LockEvent::EVENT_BEFORE_RELEASE_LOCK, $lock, $lockName);
        $lock->getDriver()->releaseLock($lockName);
        $this->dispatchAndLogEvent(LockEvent::EVENT_AFTER_RELEASE_LOCK, $lock, $lockName);
        return $return;
    }

    private function dispatchAndLogEvent($name, Lock $lock, $lockName)
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch($name, new LockEvent($lock, $this->originalService));
        }
        if ($this->logger) {
            $context = array(
                'service' => $this->originalServiceClass,
                'method' => $lock->getMethod(),
                'lock' => $lockName
            );
            $this->logger->log(\Psr\Log\LogLevel::INFO, $name, $context);
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

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

}
