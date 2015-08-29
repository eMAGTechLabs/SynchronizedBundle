<?php

namespace Sms\SynchronizedBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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
     * @var ContainerBuilder $container
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
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        if (!$container->hasParameter('synchronized.lock_path')) {
            $container->setParameter('synchronized.lock_path', '%kernel.root_dir%/synchronized.lock');
        }
        $loader->load('synchronized.xml');
        $loader->load('drivers.xml');
        $this->loadLocks($config, $container);
    }

    private function loadLocks($config, ContainerBuilder $container)
    {
        foreach ($config['locks'] as $name => $lockConfig) {
            $service = $lockConfig['service'];
            $method = $lockConfig['method'];
            $argument = $lockConfig['argument'];
            $driver = $lockConfig['driver'];
            $id = sprintf('synchronized_lock_%s_%s_%s_%s', $driver, $service, $method, $argument);
            if ($container->hasDefinition($id)) {
                throw new InvalidConfigurationException(sprintf('cannot load %s, definition %s already exists', $name, $id));
            }

            $lockDefinition = new Definition('Sms\SynchronizedBundle\Lock');
            $lockDefinition->addMethodCall('setMethod', array($method));
            $lockDefinition->addMethodCall('setArgumentIndex', array($argument));
            $lockDefinition->addMethodCall('setDriver', array(new Reference(sprintf('synchronized_driver.%s', $driver))));
            $container->addDefinitions(array($id => $lockDefinition));
            $this->decorateService($service, $id);
        }
    }

    private function decorateService($serviceId, $lockId)
    {
        $synchronizedServiceId = sprintf('synchronized.%s', $serviceId);
        if ($this->container->has($synchronizedServiceId)) {
            $definition = $this->container->findDefinition($synchronizedServiceId);
        } else {
            $definition = $this->container->register($synchronizedServiceId, 'Sms\SynchronizedBundle\Decorator');
            $definition->addArgument(new Reference(sprintf('%s.inner', $synchronizedServiceId)))
                    ->addArgument($serviceId)
                    ->setPublic(false)
                    ->setDecoratedService($serviceId);
            if ($this->container->has('event_dispatcher')) {
                $definition->addArgument(new Reference('event_dispatcher'));
            }
        }
        $definition->addMethodCall('addLock', array(new Reference($lockId)));
    }

}
