<?php

namespace PhpRbacBundle;

use PhpRbacBundle\DependencyInjection\Compiler\UserRepositoryPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use PhpRbacBundle\DependencyInjection\Compiler\DoctrineResolveTargetEntityPass;

final class PhpRbacBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new DoctrineResolveTargetEntityPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
        $container->addCompilerPass(new UserRepositoryPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
    }
}
