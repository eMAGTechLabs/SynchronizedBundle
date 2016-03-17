<?php

namespace Emag\SynchronizedBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
                ->scalarNode('prefix')->defaultValue('synchronized')->end()
                ->end();
        $this->addLocks($rootNode);

        return $treeBuilder;
    }

    private function addLocks(ArrayNodeDefinition $node)
    {

        $node->children()
                ->arrayNode('locks')
                ->requiresAtLeastOneElement()
                ->prototype('array')
                ->children()
                ->scalarNode('service')->end()
                ->scalarNode('method')->defaultNull()->end()
                ->scalarNode('argument')->defaultNull()->end()
                ->scalarNode('driver')->defaultValue('debug')->end()
                ->end()
                ->end()
                ->end()
        ;
    }

}
