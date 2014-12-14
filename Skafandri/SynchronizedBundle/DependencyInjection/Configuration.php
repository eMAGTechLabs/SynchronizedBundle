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

    const DEFAULT_ACTION = 'wait';
    const DEFAULT_ARGUMENT = null;
    const DEFAULT_RETRY_DURATION = 10000;
    const DEFAULT_RETRY_COUNT = 10;

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
                ->scalarNode('driver')->defaultValue('file')
                ->validate()
                ->ifTrue(function ($v) {
                    var_dump($v);
                    return false;
                })
                ->thenInvalid('Invalid auto generate mode value %s')
                ->end()
                ->end()
                ->scalarNode('path')->defaultValue('%kernel.root_dir%/synchronized.lock')->end()
                ->arrayNode('services')
                ->useAttributeAsKey('key')
                ->prototype('array')
                ->children()
                ->scalarNode('method')->end()
                ->scalarNode('action')->defaultValue(self::DEFAULT_ACTION)->end()
                ->scalarNode('argument')->defaultValue(self::DEFAULT_ARGUMENT)->end()
                ->scalarNode('retry_duration')->defaultValue(self::DEFAULT_RETRY_DURATION)->end()
                ->scalarNode('retry_count')->defaultValue(self::DEFAULT_RETRY_COUNT)
                ->end()
                ->end()
                ->end();

        return $treeBuilder;
    }

}
