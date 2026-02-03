<?php

namespace PhpRbacBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use PhpRbacBundle\Core\RbacCacheService;
use PhpRbacBundle\Repository\RoleRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(
    name: 'security:rbac:user:assign-role',
    description: 'Assign roles to a user',
)]
class RbacAssignUserRoleCommand extends Command
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $userEntity,
        private readonly RbacCacheService $cacheService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('security:rbac:user:assign-role')
            ->setDescription('Assign roles to a user')
            ->addArgument('userId', InputArgument::REQUIRED, 'The user Id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $rolesTmp = $this->roleRepository->findAll();

        if (empty($rolesTmp)) {
            $io = new SymfonyStyle($input, $output);
            $io->error(
                'You should install first the root nodes for both roles and permissions. '.
                'Use `php bin/console security:rbac:install` command in order to do that.'
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

        $userId = $input->getArgument('userId');
        $userRepository = $this->entityManager->getRepository($this->userEntity);

        $user = $userRepository->find($userId);

        $question = new ChoiceQuestion('Choice the roles (multiple separate by comma): ', array_keys($roles), 0);
        $question->setMultiselect(true);
        $rolePaths = $helper->ask($input, $output, $question);
        foreach ($rolePaths as $rolePath) {
            $role = $roles[$rolePath];
            $user->addRbacRole($role);
        }
        $userRepository->add($user, true);

        // Clear cache for this specific user
        $this->cacheService->clearUser((int) $userId);

        $io = new SymfonyStyle($input, $output);
        $io->success("Role(s) assigned successfully to user {$userId}. User cache cleared.");

        return Command::SUCCESS;
    }
}
