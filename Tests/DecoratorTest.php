<?php

namespace Sms\SynchronizedBundle\Tests;

use Sms\SynchronizedBundle\Decorator;
use Sms\SynchronizedBundle\Driver\Debug;
use Sms\SynchronizedBundle\Driver\File;
use Sms\SynchronizedBundle\Event\LockEvent;
use Sms\SynchronizedBundle\Lock;
use Sms\SynchronizedBundle\Tests\Stubs\TestEventDispatcher;
use Sms\SynchronizedBundle\Tests\Stubs\TestService;
use Symfony\Component\Process\Process;

class DecoratorTest extends AbstractTest
{

    public function testGetWithMethodLock()
    {
        $decorator = new Decorator(new TestService(), 'test_service');
        $lock = new Lock();
        $lock->setDriver(new Debug())->setMethod('sleep1');
        $decorator->addLock($lock);
        $decorator->sleep1();
        $decorator->sleep(0);
    }

    public function testGetWithArgumentLock()
    {
        $decorator = new Decorator(new TestService(), 'test_service');
        $lock = new Lock();
        $lock->setDriver(new Debug())->setMethod('sleep')->setArgumentIndex(1);
        $decorator->addLock($lock);
        $decorator->sleep(0, 5);
    }

    /**
     * @expectedException \Sms\SynchronizedBundle\Exception\CannotAquireLockException
     * @expectedExceptionMessage test_service_sleep
     */
    public function testFailedFileDriver()
    {
        $fileLock = new File('lock');
        $fileLock->clearLocks();
        $p = new Process('php Tests/FileLockCommand.php test:file -s 5');
        $p->start();
        sleep(1);
        $eventDispatcher = new TestEventDispatcher();
        $decorator = new Decorator(new TestService(), 'test_service');
        $decorator->setEventDispatcher($eventDispatcher);
        $lock = new Lock();
        $lock->setDriver($fileLock)->setMethod('sleep');
        $decorator->addLock($lock);
        try {
            $decorator->sleep(1);
        } catch(\Exception $exception){
            $p->stop();
            $this->assertArrayHasKey(LockEvent::EVENT_FAILURE_GET_LOCK, $eventDispatcher->getEvents());
            throw $exception;
        }
    }
    
    public function testSuccessFileDriver()
    {
        $fileLock = new File('lock');
        $fileLock->clearLocks();
        $p = new Process('/usr/bin/php Tests/FileLockCommand.php test:file -s 1');
        $p->start();
        sleep(5);
        $decorator = new Decorator(new TestService(), 'test_service');
        $lock = new Lock();
        $lock->setDriver($fileLock)->setMethod('sleep');
        $decorator->addLock($lock);
        $decorator->sleep(1);
        $p->stop();
        return $this->assertTrue(true);
    }
    
    public function testFileDriverWithArrayArgument()
    {
        $fileLock = new File('lock');
        $fileLock->clearLocks();
        $p = new Process('/usr/bin/php Tests/FileLockCommand.php test:file -s 1');
        $p->start();
        $decorator = new Decorator(new TestService(), 'test_service');
        $lock = new Lock();
        $lock->setDriver($fileLock)->setMethod('sleep')->setArgumentIndex(1);
        $decorator->addLock($lock);
        $decorator->sleep(1, array(1));
        $p->stop();
        return $this->assertTrue(true);
    }
    
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage something exception
     */
    public function testLockedMethodThatThrowsException() {
        $decorator = new Decorator(new TestService(), 'test_service');
        $lock = new Lock();
        $lock->setDriver(new Debug())->setMethod('doSomething');
        $decorator->addLock($lock);
        
        $decorator->doSomething(function() {
            throw new \Exception('something exception');
        });
    }

}
