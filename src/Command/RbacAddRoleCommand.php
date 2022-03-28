<?php

namespace PhpRbacBundle\Command;

use PhpRbacBundle\Core\Manager\RoleManager;
use PhpRbacBundle\Entity\Role;
use PhpRbacBundle\Entity\Permission;
use PhpRbacBundle\Repository\RoleRepository;
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
    name: 'security:add-role',
    description: 'Add role to RBAC system',
)]
class RbacAddRoleCommand extends Command
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
            ->setName('security:add-role')
            ->setDescription('Add role to RBAC system');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $rolesTmp = $this->roleRepository->findAll();
        $roles = [];
        foreach ($rolesTmp as $role) {
            $pathNodes = $this->roleRepository->getPath($role->getId());
            $path = "/".implode('/', $pathNodes);
            $path = str_replace("/root", "/", $path);
            $path = str_replace("//", "/", $path);
            echo $path.PHP_EOL;
            $roles[$path] = $role;
        }
        ksort($roles);

        $question = new Question('Enter the title of the role : ');
        $title = $helper->ask($input, $output, $question);
        $question = new Question('Enter the description of the role : ');
        $description = $helper->ask($input, $output, $question);
        $question = new ChoiceQuestion('Enter the parent of the role : ', array_keys($roles), 0);
        $parentPath = $helper->ask($input, $output, $question);
        $role = $this->roleManager->add($title, $description, $roles[$parentPath]->getId());
        
        return Command::SUCCESS;
    }
}
