<?php

namespace Sms\SynchronizedBundle\Tests;

use Sms\SynchronizedBundle\DependencyInjection\SynchronizedExtension;
use Sms\SynchronizedBundle\SynchronizedBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        echo(sprintf('Starting test: %s:%s', get_class($this), $this->getName()) . "\n");
    }

    protected function loadConfiguration($array, $compile = true)
    {
        $container = new ContainerBuilder(new ParameterBag(array('kernel.debug' => false,
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir' => __DIR__ . '/../../../../', // src dir
        )));
        $extension = new SynchronizedExtension();
        $extension->load($array, $container);
        $container->registerExtension($extension);
        $container->addDefinitions(array('test_service' => new Definition('stdClass')));

        $bundle = new SynchronizedBundle();
        $bundle->build($container);
        $compile && $container->compile();
        return $container;
    }

}
