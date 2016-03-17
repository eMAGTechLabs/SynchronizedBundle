<?php

namespace Emag\SynchronizedBundle\Tests;

use Emag\SynchronizedBundle\DependencyInjection\SynchronizedExtension;
use Emag\SynchronizedBundle\Event\LockEvent;
use Emag\SynchronizedBundle\Tests\Stubs\TestService;
use Symfony\Component\DependencyInjection\Definition;

class EventDispatcherTest extends AbstractTest
{

    public function testGetLockEvents()
    {
        $container = $this->getContainer();
        $container->addDefinitions(array('test_service' => new Definition('Emag\SynchronizedBundle\Tests\Stubs\TestService')));
        $eventDispatcherDefinition = new Definition('Emag\SynchronizedBundle\Tests\Stubs\TestEventDispatcher');
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
