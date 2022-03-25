<?php

namespace PhpRbacBundle\DependencyInjection;

use PhpRbacBundle\Command\SecurityInstallRbacCommand;
use PhpRbacBundle\Core\PermissionManagerInterface;
use PhpRbacBundle\Core\RbacInterface;
use PhpRbacBundle\Core\RoleManagerInterface;
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

        $container->registerForAutoconfiguration(PermissionManagerInterface::class)
            ->setPublic(true);
        $container->registerForAutoconfiguration(RoleManagerInterface::class)
            ->setPublic(true);
        $container->registerForAutoconfiguration(RbacInterface::class)
            ->setPublic(true);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yml');
    }

    public function getAlias(): string
    {
        return 'php_rbac';
    }
}
