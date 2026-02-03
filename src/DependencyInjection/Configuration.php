<?php

namespace PhpRbacBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('php_rbac');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('no_authentication_section')
                    ->children()
                        ->scalarNode('default')
                            ->info('Set default deny or allow if access control attribute is missing with aurhorized user')
                            ->defaultValue('deny')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('resolve_target_entities')
                    ->children()
                        ->scalarNode('user')
                            ->info('Set the class which implements UserInterface')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('role')
                            ->info('Set the class which implements RoleInterface')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('permission')
                            ->info('Set the class which implements PermissionInterface')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->info('Enable or disable RBAC cache')
                            ->defaultTrue()
                        ->end()
                        ->integerNode('ttl')
                            ->info('Cache TTL in seconds')
                            ->defaultValue(3600)
                            ->min(0)
                        ->end()
                        ->scalarNode('prefix')
                            ->info('Cache key prefix')
                            ->defaultValue('rbac_')
                        ->end()
                    ->end()
                ->end();
        return $treeBuilder;
    }
}
