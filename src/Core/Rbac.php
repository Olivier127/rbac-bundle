<?php

namespace PhpRbacBundle\Core;

use Webmozart\Assert\Assert;
use PhpRbacBundle\Entity\RoleInterface;
use PhpRbacBundle\Core\RoleManagerInterface;
use PhpRbacBundle\Entity\PermissionInterface;
use PhpRbacBundle\Core\PermissionManagerInterface;

class Rbac implements RbacInterface
{
    public function __construct(
        private PermissionManagerInterface $permissionManager,
        private RoleManagerInterface $roleManager
    ) {
    }

    public function hasPermission(string|int|PermissionInterface $permission, mixed $userId): bool
    {
        Assert::notEmpty($userId);

        $permissionId = $permission;
        if (is_object($permission)) {
            $permissionId = $permission->getId();
        } elseif (is_string($permission)) {
            $permissionId = $this->permissionManager->getPathId($permission);
        }

        return $this->permissionManager->hasPermission($permissionId, $userId);
    }

    public function hasRole(string|int|RoleInterface $role, mixed $userId): bool
    {
        Assert::notEmpty($userId);

        $roleId = $role;
        if (is_object($role)) {
            $roleId = $role->getId();
        } elseif (is_string($role)) {
            $roleId = $this->roleManager->getPathId($role);
        }

        return $this->roleManager->hasRole($roleId, $userId);
    }
}
