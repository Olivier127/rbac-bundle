<?php

namespace PhpRbacBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use PhpRbacBundle\DependencyInjection\Compiler\DoctrineResolveTargetEntityPass;
use PhpRbacBundle\DependencyInjection\PhpRbacExtension;

final class PhpRbacBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new DoctrineResolveTargetEntityPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new PhpRbacExtension();
        }
        return $this->extension;
    }
}
