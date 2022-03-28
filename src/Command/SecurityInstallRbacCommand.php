<?php

namespace PhpRbacBundle\Command;

use PhpRbacBundle\Entity\Role;
use PhpRbacBundle\Entity\Permission;
use PhpRbacBundle\Repository\RoleRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use PhpRbacBundle\Repository\PermissionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'security:install-rbac',
    description: 'first set of data for rbac installation',
)]
class SecurityInstallRbacCommand extends Command
{
    public function __construct(
        private PermissionRepository $permissionRepository,
        private RoleRepository $roleRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('security:install-rbac')
            ->setDescription('first set of data for rbac installation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Role permission installation');
        $permission = new Permission;
        $permission->setId(1)
            ->setTitle('root')
            ->setDescription("Root")
            ->setLeft(0)
            ->setRight(1);
        $this->permissionRepository->add($permission, true);
        $io->note('Role root installation');
        $role = new Role;
        $role->setId(1)
            ->setTitle('root')
            ->setDescription("Root")
            ->setLeft(0)
            ->setRight(1)
            ->addPermission($permission);
        $this->roleRepository->add($role, true);

        $io->success('Done');

        return Command::SUCCESS;
    }
}
