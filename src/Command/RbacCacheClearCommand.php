<?php

namespace PhpRbacBundle\Command;

use PhpRbacBundle\Core\RbacCacheService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'security:rbac:cache:clear',
    description: 'Clear RBAC cache (permissions and roles)',
)]
class RbacCacheClearCommand extends Command
{
    public function __construct(
        private readonly RbacCacheService $cacheService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('security:rbac:cache:clear')
            ->setDescription('Clear RBAC cache (permissions and roles)')
            ->addOption(
                'permissions',
                'p',
                InputOption::VALUE_NONE,
                'Clear only permissions cache'
            )
            ->addOption(
                'roles',
                'r',
                InputOption::VALUE_NONE,
                'Clear only roles cache'
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_REQUIRED,
                'Clear cache for a specific user ID'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->cacheService->isEnabled()) {
            $io->warning('RBAC cache is disabled in configuration.');
            return Command::SUCCESS;
        }

        $permissionsOnly = $input->getOption('permissions');
        $rolesOnly = $input->getOption('roles');
        $userId = $input->getOption('user');

        // Clear cache for specific user
        if ($userId !== null) {
            $userId = (int) $userId;
            
            if ($permissionsOnly) {
                $this->cacheService->clearPermissions($userId);
                $io->success("Permissions cache cleared for user ID: {$userId}");
            } elseif ($rolesOnly) {
                $this->cacheService->clearRoles($userId);
                $io->success("Roles cache cleared for user ID: {$userId}");
            } else {
                $this->cacheService->clearUser($userId);
                $io->success("All RBAC cache cleared for user ID: {$userId}");
            }
            
            return Command::SUCCESS;
        }

        // Clear specific cache type
        if ($permissionsOnly) {
            $this->cacheService->clearPermissions();
            $io->success('All permissions cache cleared successfully.');
            return Command::SUCCESS;
        }

        if ($rolesOnly) {
            $this->cacheService->clearRoles();
            $io->success('All roles cache cleared successfully.');
            return Command::SUCCESS;
        }

        // Clear all RBAC cache
        $this->cacheService->clearAll();
        $io->success('All RBAC cache (permissions and roles) cleared successfully.');

        return Command::SUCCESS;
    }
}
