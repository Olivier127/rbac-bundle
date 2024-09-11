<?php

namespace PhpRbacBundle\Command;

use PhpRbacBundle\Repository\RoleRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use PhpRbacBundle\Repository\PermissionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'security:rbac:install',
    description: 'first set of data for rbac installation',
)]
class SecurityInstallRbacCommand extends Command
{
    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly RoleRepository $roleRepository
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
        $this->permissionRepository->initTable();
        $io->note('Role root installation');
        $this->roleRepository->initTable();

        $io->success('Done');

        return Command::SUCCESS;
    }
}
