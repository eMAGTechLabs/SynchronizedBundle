<?php

namespace Sms\SynchronizedBundle\Tests;

use Sms\SynchronizedBundle\Decorator;
use Sms\SynchronizedBundle\DependencyInjection\SynchronizedExtension;
use Sms\SynchronizedBundle\Driver\Debug;
use Sms\SynchronizedBundle\Lock;
use Sms\SynchronizedBundle\Tests\Stubs\TestLogger;
use Sms\SynchronizedBundle\Tests\Stubs\TestService;
use Symfony\Component\DependencyInjection\Definition;

class LoggerTest extends AbstractTest
{

    private $expectedLogs = array(
        array(
            'level' => 'info',
            'message' => 'synchronized.event.before_get_lock',
            array(
                'service' => 'Sms\SynchronizedBundle\Tests\Stubs\TestService',
                'method' => 'doNothing',
                'lock' => 'test_service_doNothing'
            )
        ),
        array(
            'level' => 'info',
            'message' => 'synchronized.event.success_get_lock',
            array(
                'service' => 'Sms\SynchronizedBundle\Tests\Stubs\TestService',
                'method' => 'doNothing',
                'lock' => 'test_service_doNothing'
            )
        ),
        array(
            'level' => 'info',
            'message' => 'synchronized.event.before_release_lock',
            array(
                'service' => 'Sms\SynchronizedBundle\Tests\Stubs\TestService',
                'method' => 'doNothing',
                'lock' => 'test_service_doNothing'
            )
        ),
        array(
            'level' => 'info',
            'message' => 'synchronized.event.after_release_lock',
            array(
                'service' => 'Sms\SynchronizedBundle\Tests\Stubs\TestService',
                'method' => 'doNothing',
                'lock' => 'test_service_doNothing'
            )
        )
    );

    public function testLogger()
    {
        $decorator = new Decorator(new TestService(), 'test_service');
        $lock = new Lock();
        $lock->setDriver(new Debug())->setMethod('doNothing');
        $decorator->addLock($lock);
        $logger = new TestLogger();
        $decorator->setLogger($logger);

        $decorator->doNothing();

        $this->assertEquals($this->expectedLogs, $logger->getLogs());
    }

    public function testLoadLogger()
    {
        $container = $this->getContainer();
        $container->addDefinitions(array('test_service' => new Definition('Sms\SynchronizedBundle\Tests\Stubs\TestService')));
        $loggerDefinition = new Definition('Sms\SynchronizedBundle\Tests\Stubs\TestLogger');
        $container->addDefinitions(array('logger' => $loggerDefinition));

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
        $logger = $container->get('logger');
        $testService->doNothing();
        $this->assertEquals($this->expectedLogs, $logger->getLogs());
    }

}
