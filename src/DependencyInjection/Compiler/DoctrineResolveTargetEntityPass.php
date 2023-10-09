<?php

namespace PhpRbacBundle\DependencyInjection\Compiler;

use PhpRbacBundle\Entity\PermissionInterface;
use PhpRbacBundle\Entity\RoleInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineResolveTargetEntityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');

        $definition->addMethodCall('addResolveTargetEntity', [PermissionInterface::class, $container->getParameter('php_rbac.resolve_target_entities.permission'), []]);
        $definition->addMethodCall('addResolveTargetEntity', [RoleInterface::class, $container->getParameter('php_rbac.resolve_target_entities.role'), []]);

        $definition->addTag('doctrine.event_subscriber');
    }
}
