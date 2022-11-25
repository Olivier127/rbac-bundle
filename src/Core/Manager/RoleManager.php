<?php

namespace PhpRbacBundle\Core\Manager;

use PhpRbacBundle\Entity\RoleInterface;
use PhpRbacBundle\Repository\RoleRepository;
use PhpRbacBundle\Entity\PermissionInterface;
use PhpRbacBundle\Repository\PermissionRepository;

/**
 * @property RoleRepository $repository
 */
class RoleManager extends NodeManager implements RoleManagerInterface
{
    public function __construct(
        private PermissionManager $permissionManager,
        RoleRepository $roleRepository
    ) {
        parent::__construct($roleRepository);
    }

    public function remove(RoleInterface $role): bool
    {
        return $this->repository->deleteNode($role->getId());
    }

    public function removeRecursively(RoleInterface $role): bool
    {
        return $this->repository->deleteSubtree($role->getId());
    }

    public function assignPermission(RoleInterface $role, string $permission)
    {
        $nodeId = $this->permissionManager->getPathId($permission);
        $node = $this->permissionManager->getNode($nodeId);
        $role->addPermission($node);
        $this->repository->add($role, true);
    }

    public function unassignPermission(RoleInterface $role, string $permission)
    {
        $nodeId = $this->permissionManager->getPathId($permission);
        $node = $this->permissionManager->getNode($nodeId);
        $role->removePermission($node);
        $this->repository->add($role, true);
    }

    public function unassignPermissions(RoleInterface $role): bool
    {
        $role = $this->repository->deletePermissions($role);

        return empty($role->getPermissions());
    }

    public function hasPermission(int $roleId, int $permissionId): bool
    {
        return $this->repository->hasPermission($roleId, $permissionId);
    }

    public function hasRole(int $roleId, mixed $userId): bool
    {
        return $this->repository->hasRole($roleId, $userId);
    }
}
