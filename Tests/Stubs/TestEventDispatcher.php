<?php

namespace Emag\SynchronizedBundle\Tests\Stubs;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestEventDispatcher implements EventDispatcherInterface
{

    private $events = array();

    public function dispatch($eventName, Event $event = null)
    {
        $this->events[$eventName] = $event;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function addListener($eventName, $listener, $priority = 0)
    {

    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {

    }

    public function getListeners($eventName = null)
    {

    }

    public function hasListeners($eventName = null)
    {

    }

    public function removeListener($eventName, $listener)
    {

    }

    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {

    }

    public function getListenerPriority($eventName, $listener)
    {

    }
}
