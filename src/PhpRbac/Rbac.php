<?php

namespace PhpRbac;

use Webmozart\Assert\Assert;
use PhpRbac\Entity\RoleInterface;
use PhpRbac\Core\RoleManagerInterface;
use PhpRbac\Core\UserManagerInterface;
use PhpRbac\Entity\PermissionInterface;
use PhpRbac\Core\PermissionManagerInterface;

class Rbac implements RbacInterface
{
    public function __construct(
        private PermissionManagerInterface $permissionManager,
        private RoleManagerInterface $roleManager,
        private UserManagerInterface $userManager
    ) {
    }

    public function hasPermission(string|int|PermissionInterface $permission, mixed $userId): bool
    {
        Assert::notEmpty($userId);

        $permissionId = $permission;
        if (is_object($permission)) {
            $permissionId = $permission->getId();
        } elseif (is_string($permission)) {
            $permissionId = $this->permissionManager->returnId($permission);
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
            $roleId = $this->roleManager->returnId($role);
        }

        return $this->roleManager->hasRole($roleId, $userId);
    }
}
