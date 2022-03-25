<?php

namespace PhpRbacBundle\Core;

use PhpRbacBundle\Entity\RoleInterface;
use PhpRbacBundle\Repository\PermissionRepository;
use PhpRbacBundle\Entity\PermissionInterface;

/**
 * @property PermissionRepository $repository
 */
class PermissionManager extends NodeManager implements PermissionManagerInterface
{
    public function __construct(PermissionRepository $permissionRepository)
    {
        parent::__construct($permissionRepository);
    }

    public function remove(PermissionInterface $permission): bool
    {
        return $this->repository->deleteNode($permission->getId());
    }

    public function removeRecursively(PermissionInterface $permission): bool
    {
        return $this->repository->deleteSubtree($permission->getId());
    }

    public function hasPermission(int $permissionId, mixed $userId): bool
    {
        return $this->repository->hasPermission($permissionId, $userId);
    }
}