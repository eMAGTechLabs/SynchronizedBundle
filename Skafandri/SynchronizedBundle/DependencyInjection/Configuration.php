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
    const DEFAULT_RETRY_DURATION = 100000;
    const DEFAULT_RETRY_COUNT = 50;
    const DEFAULT_PATH = '%kernel.root_dir%/synchronized.lock';
    const DEFAULT_MEMCACHED_SERVICE = 'memcached';

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
                ->scalarNode('memcached_service')->defaultValue('@memcached')
                ->end()
                ->scalarNode('path')->defaultValue(self::DEFAULT_PATH)->end()
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
