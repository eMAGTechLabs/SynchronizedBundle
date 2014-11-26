<?php

namespace Skafandri\SynchronizedBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        /**
         * @var TreeBuilder $treeBuilder
         */
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('synchronized');
        
        $rootNode->children()
                ->scalarNode('driver')->defaultValue('file')->end()
                ->scalarNode('path')->defaultValue('"%kernel.root_dir%/synchronized.lock')->end()
                ->arrayNode('services')
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('method')->end()
                        ->scalarNode('action')->defaultValue('wait')->end()
                        ->scalarNode('argument')->defaultValue(false)->end()
                ->end()
            ->end()
                ;

        return $treeBuilder;
    }
}
