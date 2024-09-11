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
                ->end();
        return $treeBuilder;
    }
}
