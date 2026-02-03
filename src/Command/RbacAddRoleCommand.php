<?php

namespace PhpRbacBundle\Command;

use PhpRbacBundle\Core\RbacCacheService;
use PhpRbacBundle\Core\Manager\RoleManager;
use PhpRbacBundle\Repository\RoleRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(
    name: 'security:rbac:role:add',
    description: 'Add role to RBAC system',
)]
class RbacAddRoleCommand extends Command
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly RoleManager $roleManager,
        private readonly RbacCacheService $cacheService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('security:rbac:role:add')
            ->setDescription('Add role to RBAC system');
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
            $path = '/' . implode('/', $pathNodes);
            $path = str_replace('/root', '/', $path);
            $path = str_replace('//', '/', $path);
            $roles[$path] = $role;
        }
        ksort($roles);

        $question = new Question('Enter the code of the role : ');
        $code = $helper->ask($input, $output, $question);
        $question = new Question('Enter the description of the role : ');
        $description = $helper->ask($input, $output, $question);
        $question = new ChoiceQuestion('Enter the parent of the role : ', array_keys($roles), 0);
        $parentPath = $helper->ask($input, $output, $question);
        $role = $this->roleManager->add($code, $description, $roles[$parentPath]->getId());

        // Clear cache after adding role
        $this->cacheService->clearRoles();

        $io = new SymfonyStyle($input, $output);
        $io->success('Role added successfully. Cache cleared.');

        return Command::SUCCESS;
    }
}
