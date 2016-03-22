<?php

namespace Emag\SynchronizedBundle\Tests;

use Emag\SynchronizedBundle\DependencyInjection\SynchronizedExtension;
use Emag\SynchronizedBundle\SynchronizedBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        echo(sprintf('[%s] Starting test: %s:%s', date('Y-m-d H:i:s'), get_class($this), $this->getName()) . "\n");
    }

    protected function loadConfiguration($container, $array, $compile = true)
    {
        $extension = new SynchronizedExtension();
        $extension->load($array, $container);
        $container->registerExtension($extension);
        $container->addDefinitions(array('test_service' => new Definition('Emag\SynchronizedBundle\Tests\Stubs\TestService')));


        $bundle = new SynchronizedBundle();
        $bundle->build($container);
        $compile && $container->compile();
        return $container;
    }

    protected function getContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array('kernel.debug' => false,
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir' => __DIR__ . '/../../../../', // src dir
        )));

        $container->addDefinitions(array('filesystem' => new Definition(get_class($this->getMockBuilder(Filesystem::class)->getMock()))));
        return $container;
    }

}
