<?php

namespace PhpRbacBundle\Command;

use PhpRbacBundle\Core\Manager\PermissionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use PhpRbacBundle\Repository\PermissionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(
    name: 'security:rbac:permission:add',
    description: 'Add permission to RBAC system',
)]
class RbacAddPermissionCommand extends Command
{
    public function __construct(
        private PermissionRepository $permissionRepository,
        private PermissionManager $permissionManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('security:rbac:permission:add')
            ->setDescription('Add permission to RBAC system');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $permissionsTmp = $this->permissionRepository->findAll();
        $permissions = [];
        foreach ($permissionsTmp as $permission) {
            $pathNodes = $this->permissionRepository->getPath($permission->getId());
            $path = "/" . implode('/', $pathNodes);
            $path = str_replace("/root", "/", $path);
            $path = str_replace("//", "/", $path);
            $permissions[$path] = $permission;
        }
        ksort($permissions);

        $question = new Question('Enter the code of the permission : ');
        $code = $helper->ask($input, $output, $question);
        $question = new Question('Enter the description of the permission : ');
        $description = $helper->ask($input, $output, $question);
        $question = new ChoiceQuestion('Enter the parent of the permission : ', array_keys($permissions), 0);
        $parentPath = $helper->ask($input, $output, $question);
        $permission = $this->permissionManager->add($code, $description, $permissions[$parentPath]->getId());

        return Command::SUCCESS;
    }
}
