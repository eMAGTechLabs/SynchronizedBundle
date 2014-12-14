<?php

namespace Skafandri\SynchronizedBundle\DependencyInjection;

use Skafandri\SynchronizedBundle\Driver\DriverInterface;
use Skafandri\SynchronizedBundle\Exception\InvalidDriverException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
    private $validDrivers = array(
        DriverInterface::DRIVER_FILE,
        DriverInterface::DRIVER_DOCTRINE,
        DriverInterface::DRIVER_MEMCACHED,
        DriverInterface::DRIVER_REDIS
    );

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
            $this->decorateService($serviceId, $options);
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
            default:
                throw new InvalidDriverException(sprintf('Invalid driver "%s", valid drivers are (%s)', $config['driver'], join(', ', $this->validDrivers)));
        }
        $driver = $this->container->register('synchronized.driver', $driverClass);
        foreach ($arguments as $argument) {
            $driver->addArgument($argument);
        }
    }

    private function decorateService($serviceId, $options)
    {
        $method = $options['method'];
        $action = $options['action'];
        $argument = $options['argument'];
        $retryDuration = $options['retry_duration'];
        $retryCount = $options['retry_count'];

        $synchronizedServiceId = sprintf('%s.synchronized', $serviceId);
        $definition = $this->container->register($synchronizedServiceId, 'Skafandri\SynchronizedBundle\Service\SynchronizedService');
        $definition->addArgument(new Reference(sprintf('%s.inner', $synchronizedServiceId)))
                ->addArgument(new Reference('synchronized.driver'))
                ->addArgument($method)
                ->addArgument($action)
                ->addArgument($argument)
                ->addArgument($retryDuration)
                ->addArgument($retryCount)
                ->setPublic(false)
                ->setDecoratedService($serviceId);
    }

}
