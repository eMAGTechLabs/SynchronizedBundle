<?php

namespace Emag\SynchronizedBundle\Event;

use Emag\SynchronizedBundle\Lock;
use Symfony\Component\EventDispatcher\Event;

class LockEvent extends Event
{

    const EVENT_BEFORE_GET_LOCK = 'synchronized.event.before_get_lock';
    const EVENT_SUCCESS_GET_LOCK = 'synchronized.event.success_get_lock';
    const EVENT_FAILURE_GET_LOCK = 'synchronized.event.failure_get_lock';
    const EVENT_BEFORE_RELEASE_LOCK = 'synchronized.event.before_release_lock';
    const EVENT_AFTER_RELEASE_LOCK = 'synchronized.event.after_release_lock';

    /**
     *
     * @var Lock
     */
    private $lock;
    private $decorated;

    public function __construct(Lock $lock, $decorated)
    {
        $this->lock = $lock;
        $this->decorated = $decorated;
    }

    public function getLock()
    {
        return $this->lock;
    }

    public function getDecorated()
    {
        return $this->decorated;
    }

}
