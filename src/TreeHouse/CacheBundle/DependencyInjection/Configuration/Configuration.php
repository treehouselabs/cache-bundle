<?php

namespace TreeHouse\CacheBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use TreeHouse\Cache\CacheInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('treehouse_cache');

        $this->addClientsSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Adds the treehouse_cache.clients configuration
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addClientsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('client')
            ->children()
                ->arrayNode('clients')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name', false)
                    ->prototype('array')
                        ->children()
                            ->enumNode('type')
                                ->isRequired()
                                ->values(['phpredis', 'memcached'])
                                ->info('The type of cache')
                            ->end()

                            ->scalarNode('dsn')
                                ->isRequired()
                                ->info(
                                    'DSN of the cache, prefix this with the protocol, ' .
                                    'ie: redis:///var/run/redis.sock or memcached://localhost:11211'
                                )
                            ->end()

                            ->arrayNode('connection')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->booleanNode('persistent')
                                        ->defaultTrue()
                                        ->info('Whether to use a persistent connection. Only available for redis cache')
                                    ->end()
                                    ->scalarNode('timeout')
                                        ->defaultValue(5)
                                        ->info('Timeout to use when connecting')
                                    ->end()
                                ->end()
                            ->end()

                            ->scalarNode('prefix')
                                ->defaultNull()
                                ->info('Prefix to use for the cached keys')
                            ->end()

                            ->enumNode('serializer')
                                ->defaultValue(CacheInterface::SERIALIZE_AUTO)
                                ->values([
                                    CacheInterface::SERIALIZE_AUTO,
                                    CacheInterface::SERIALIZE_IGBINARY,
                                    CacheInterface::SERIALIZE_JSON,
                                    CacheInterface::SERIALIZE_PHP,
                                ])
                            ->end()

                            ->scalarNode('serializer_class')
                            ->end()

                            ->booleanNode('in_memory')
                                ->defaultTrue()
                                ->info('Whether to wrap the cache in an in-memory caching decorator')
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('orm')
                    ->children()
                        ->scalarNode('client')
                            ->info('The cache client you want to use for the ORM cache')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;
    }
}
