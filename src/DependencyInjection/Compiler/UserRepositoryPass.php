<?php

namespace PhpRbacBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class UserRepositoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('PhpRbacBundle\Command\RbacAssignUserRoleCommand');
        $definition->setArgument(
            '$userRepository',
            new Reference($container->getParameter('php_rbac.user_repository_class'))
        );
    }
}
