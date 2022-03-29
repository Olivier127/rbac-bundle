<?php

namespace PhpRbacBundle\DependencyInjection;

use PhpRbacBundle\Command\SecurityInstallRbacCommand;
use PhpRbacBundle\Core\PermissionManagerInterface;
use PhpRbacBundle\Core\RbacInterface;
use PhpRbacBundle\Core\RoleManagerInterface;
use PhpRbacBundle\EventSubscriber\AccessControlDriver;
use Symfony\Component\Config\FileLocator;
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

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yml');

        $container->registerForAutoconfiguration(PermissionManagerInterface::class)
            ->setPublic(true);
        $container->registerForAutoconfiguration(RoleManagerInterface::class)
            ->setPublic(true);
        $container->registerForAutoconfiguration(RbacInterface::class)
            ->setPublic(true);

        $definition = $container->getDefinition(AccessControlDriver::class);
        $definition->addMethodCall('load', [$config]);
    }

    public function getAlias(): string
    {
        return 'php_rbac';
    }
}
