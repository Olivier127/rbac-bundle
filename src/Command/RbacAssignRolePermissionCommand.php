<?php

namespace PhpRbacBundle\Command;

use PhpRbacBundle\Core\Manager\RoleManager;
use PhpRbacBundle\Repository\RoleRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use PhpRbacBundle\Repository\PermissionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(
    name: 'security:rbac:role:assign-permission',
    description: 'Assign a permission to a role',
)]
class RbacAssignRolePermissionCommand extends Command
{
    public function __construct(
        private PermissionRepository $permissionRepository,
        private RoleRepository $roleRepository,
        private RoleManager $roleManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('security:rbac:role:assign-permission')
            ->setDescription('Assign a permission to a role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $rolesTmp = $this->roleRepository->findAll();

        if (empty($rolesTmp)) {
            $io = new SymfonyStyle($input, $output);
            $io->error(
                'You should install first the root nodes for both roles and permissions. '.
                'Use `php bin/console security:rbac:install` command to do that.'
            );

            return Command::INVALID;
        }

        $roles = [];
        foreach ($rolesTmp as $role) {
            $pathNodes = $this->roleRepository->getPath($role->getId());
            $path = '/'.implode('/', $pathNodes);
            $path = str_replace('/root', '/', $path);
            $path = str_replace('//', '/', $path);
            $roles[$path] = $role;
        }
        ksort($roles);

        $permissionsTmp = $this->permissionRepository->findAll();
        $permissions = [];
        foreach ($permissionsTmp as $permission) {
            $pathNodes = $this->permissionRepository->getPath($permission->getId());
            $path = '/'.implode('/', $pathNodes);
            $path = str_replace('/root', '/', $path);
            $path = str_replace('//', '/', $path);
            $permissions[$path] = $permission;
        }
        ksort($permissions);

        $question = new ChoiceQuestion('Choice the role : ', array_keys($roles), 0);
        $rolePath = $helper->ask($input, $output, $question);
        $question = new ChoiceQuestion(
            'Choice the permission (multiple separate by comma): ',
            array_keys($permissions),
            0
        );
        $question->setMultiselect(true);
        $permissionPaths = $helper->ask($input, $output, $question);
        foreach ($permissionPaths as $permissionPath) {
            $this->roleManager->assignPermission($roles[$rolePath], $permissionPath);
        }

        return Command::SUCCESS;
    }
}
