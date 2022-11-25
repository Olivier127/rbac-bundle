<?php

namespace PhpRbacBundle\DependencyInjection;

use PhpRbacBundle\Core\RbacInterface;
use Symfony\Component\Config\FileLocator;
use PhpRbacBundle\Core\RoleManagerInterface;
use PhpRbacBundle\Core\PermissionManagerInterface;
use PhpRbacBundle\Entity\PermissionInterface;
use PhpRbacBundle\Entity\RoleInterface;
use PhpRbacBundle\EventSubscriber\AccessControlDriver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PhpRbacExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $definition = $container->getDefinition(AccessControlDriver::class);
        $definition->addMethodCall('load', [$config]);

        $container->setParameter('php_rbac.resolve_target_entities.permission', $config['resolve_target_entities']['permission']);
        $container->setParameter('php_rbac.resolve_target_entities.role', $config['resolve_target_entities']['role']);
    }

    public function getAlias(): string
    {
        return 'php_rbac';
    }
}
