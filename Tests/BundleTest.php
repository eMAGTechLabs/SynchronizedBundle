<?php

namespace Emag\SynchronizedBundle\Tests;

use Symfony\Component\DependencyInjection\Definition;

class BundleTest extends AbstractTest
{

    public function testFullConfiguration()
    {
        $this->loadConfiguration($this->getContainer(), array(array(
                'prefix' => 'test',
                'locks' => array('lock1' => array(
                        'service' => 'test_service',
                        'method' => 'method',
                        'argument' => 'argument',
                        'driver' => 'debug'
                    ))
        )));
        
        $this->assertTrue(true);
    }

    public function testDefaultConfiguration()
    {
        $container = $this->loadConfiguration($this->getContainer(), array(array(
                'locks' => array(
                    'lock1' => array(
                        'service' => 'test_service',
                    )
                )
        )));

        $this->assertEquals($container->getDefinition('synchronized_driver.debug')->getClass(), 'Emag\SynchronizedBundle\Driver\Debug');
    }

    public function testFileDriverConfiguration()
    {
        $container = $this->loadConfiguration($this->getContainer(), array(array(
                'locks' => array('lock1' => array(
                        'service' => 'test_service',
                        'driver' => 'file'
                    ))
        )));
        $this->assertEquals($container->getDefinition('synchronized_driver.file')->getClass(), 'Emag\SynchronizedBundle\Driver\File');
    }
    
    public function testSameServiceDifferentMethodsLocks()
    {
        $container = $this->loadConfiguration($this->getContainer(), array(array(
                'locks' => array(
                    'lock1' => array(
                        'service' => 'test_service',
                        'method' => '1'
                    ),
                    'lock2' => array(
                        'service' => 'test_service',
                        'method' => '2'
                    )
                )
        )));
        $this->assertTrue(true);
    }

    /**
     * @expectedException Emag\SynchronizedBundle\Exception\InvalidDriverException
     * @expectedExceptionMessage Class stdClass must implement Emag\SynchronizedBundle\Driver\DriverInterface in order to be defined as driver
     */
    public function testInvalidDriverClassConfiguration()
    {
        $container = $this->loadConfiguration($this->getContainer(), array(array(
                'locks' => array('lock1' => array(
                        'service' => 'test_service',
                        'driver' => 'invalid'
                    ))
        )), false);
        $definition = new Definition('stdClass');
        $definition->addTag('synchronized.driver', array('type' => 'invalid'));
        $container->addDefinitions(array('synchronized_driver.invalid' => $definition));
        $container->compile();
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage cannot load lock2, definition synchronized_lock_debug_test_service__ already exists
     */
    public function testSameServiceLocks()
    {
        $container = $this->loadConfiguration($this->getContainer(), array(array(
                'locks' => array(
                    'lock1' => array(
                        'service' => 'test_service',
                    ),
                    'lock2' => array(
                        'service' => 'test_service',
                    )
                )
        )));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage cannot load lock2, definition synchronized_lock_debug_test_service_method_ already exists
     */
    public function testSameServiceMethodLocks()
    {
        $container = $this->loadConfiguration($this->getContainer(), array(array(
                'locks' => array(
                    'lock1' => array(
                        'service' => 'test_service',
                        'method' => 'method'
                    ),
                    'lock2' => array(
                        'service' => 'test_service',
                        'method' => 'method'
                    )
                )
        )));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage cannot load lock2, definition synchronized_lock_debug_service1_method_argument already exists
     */
    public function testSameServiceMethodArgumentLocks()
    {
        $container = $this->loadConfiguration($this->getContainer(), array(array(
                'locks' => array(
                    'lock1' => array(
                        'service' => 'service1',
                        'method' => 'method',
                        'argument' => 'argument'
                    ),
                    'lock2' => array(
                        'service' => 'service1',
                        'method' => 'method',
                        'argument' => 'argument'
                    )
                )
        )));
    }

    
    public function testRegisterDriver()
    {
        $container = $this->loadConfiguration($this->getContainer(), array(array(
                'locks' => array('lock1' => array(
                        'service' => 'test_service',
                        'driver' => 'new'
                    ))
        )), false);
        $definition = new Definition('Emag\SynchronizedBundle\Driver\Debug');
        $definition->addTag('synchronized.driver', array('type' => 'new'));
        $container->addDefinitions(array('new' => $definition));
        $container->compile();
        $this->assertTrue($container->has('synchronized_driver.new'));
    }
}
