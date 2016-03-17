<?php

namespace Emag\SynchronizedBundle\DependencyInjection\Compiler;

use Emag\SynchronizedBundle\Exception\InvalidDriverException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DriversPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('synchronized.driver') as $id => $tags) {
            $definition = $container->getDefinition($id);
            if (!in_array('Emag\SynchronizedBundle\Driver\DriverInterface', class_implements($definition->getClass()))) {
                throw new InvalidDriverException(sprintf('Class %s must implement Emag\SynchronizedBundle\Driver\DriverInterface in order to be defined as driver', $definition->getClass()));
            }
            foreach ($tags as $tag) {
                $alias = sprintf('synchronized_driver.%s', $tag['type']);
                if ($alias !== $id) {
                    $container->setAlias($alias, $id);
                }
            }
        }
    }

}
