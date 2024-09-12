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
        $resolveTargetEntityListener = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');

        $resolveTargetEntityListener->addMethodCall(
            'addResolveTargetEntity',
            [PermissionInterface::class, $container->getParameter('php_rbac.resolve_target_entities.permission'), []]
        );
        $resolveTargetEntityListener->addMethodCall(
            'addResolveTargetEntity',
            [RoleInterface::class, $container->getParameter('php_rbac.resolve_target_entities.role'), []]
        );

        $resolveTargetEntityListener
            ->addTag('doctrine.event_listener', ['event' => Events::loadClassMetadata])
            ->addTag('doctrine.event_listener', ['event' => Events::onClassMetadataNotFound]);

        $definitionCommand = $container->findDefinition('PhpRbacBundle\Command\RbacAssignUserRoleCommand');
        $definitionCommand->setArgument(
            '$userEntity',
            $container->getParameter('php_rbac.resolve_target_entities.user')
        );
    }
}
