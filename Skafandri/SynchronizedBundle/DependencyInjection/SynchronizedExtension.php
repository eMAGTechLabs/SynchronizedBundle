<?php

namespace Skafandri\SynchronizedBundle\DependencyInjection;

use Skafandri\SynchronizedBundle\Driver\DriverInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Container;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SynchronizedExtension extends Extension
{

    /**
     *
     * @var Container $container
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        //echo "<pre>";print_r($config);
        $this->loadDriver($config);
        $this->loadServices($config);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }

    private function loadServices($config)
    {
        foreach ($config['services'] as $serviceId => $options) {
            $this->decorateService($serviceId, $options['method'], $options['action'], $options['argument']);
        }
    }

    private function loadDriver($config)
    {
        $driverClass = null;
        $arguments = array();
        switch ($config['driver']) {
            case DriverInterface::DRIVER_FILE:
                $driverClass = 'Skafandri\SynchronizedBundle\Driver\File';
                $arguments[] = $config['path'];
                break;
        }
        $driver = $this->container->register('synchronized.driver', $driverClass);
        foreach ($arguments as $argument) {
            $driver->addArgument($argument);
        }
    }

    private function decorateService($serviceId, $method, $action, $argument)
    {
        $synchronizedServiceId = sprintf('%s.synchronized', $serviceId);
        $this->container->register($synchronizedServiceId, 'Skafandri\SynchronizedBundle\Service\SynchronizedService')
                ->addArgument(new Reference(sprintf('%s.inner', $synchronizedServiceId)))
                ->addArgument($method)
                ->addArgument(new Reference('synchronized.driver'))
                ->addArgument($action)
                ->addArgument($argument)
                ->setPublic(false)
                ->setDecoratedService($serviceId);
    }

}
