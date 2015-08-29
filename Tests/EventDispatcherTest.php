<?php

namespace Sms\SynchronizedBundle\Tests;

use Sms\SynchronizedBundle\DependencyInjection\SynchronizedExtension;
use Sms\SynchronizedBundle\Event\LockEvent;
use Sms\SynchronizedBundle\Tests\Stubs\TestService;
use Symfony\Component\DependencyInjection\Definition;

class EventDispatcherTest extends AbstractTest
{

    public function testGetLockEvents()
    {
        $container = $this->getContainer();
        $container->addDefinitions(array('test_service' => new Definition('Sms\SynchronizedBundle\Tests\Stubs\TestService')));
        $eventDispatcherDefinition = new Definition('Sms\SynchronizedBundle\Tests\Stubs\TestEventDispatcher');
        $container->addDefinitions(array('event_dispatcher' => $eventDispatcherDefinition));
        
        $extension = new SynchronizedExtension();
        $extension->load(array(array(
            'locks' => array('lock1' => array(
                    'service' => 'test_service',
                    'method' => 'doNothing',
                    'driver' => 'debug'
                )))), $container);
        $container->registerExtension($extension);
        $container->compile();
        
        $testService = $container->get('test_service');
        $eventDispatcher = $container->get('event_dispatcher');
        $testService->doNothing();
        $events = $eventDispatcher->getEvents();
        $this->assertArrayHasKey(LockEvent::EVENT_BEFORE_GET_LOCK, $events);
        $this->assertArrayHasKey(LockEvent::EVENT_SUCCESS_GET_LOCK, $events);
        $this->assertArrayHasKey(LockEvent::EVENT_BEFORE_RELEASE_LOCK, $events);
        $this->assertArrayHasKey(LockEvent::EVENT_AFTER_RELEASE_LOCK, $events);
        
        $event = $events[LockEvent::EVENT_BEFORE_GET_LOCK];
        $this->assertEquals($event->getLock()->getMethod(), 'doNothing');
        $this->assertTrue($event->getDecorated() instanceof TestService);
    }

}
