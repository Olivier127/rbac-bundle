<?php

namespace PhpRbacBundle\Core;

use Webmozart\Assert\Assert;
use PhpRbacBundle\Entity\RoleInterface;
use PhpRbacBundle\Core\Manager\RoleManagerInterface;
use PhpRbacBundle\Entity\PermissionInterface;
use PhpRbacBundle\Core\Manager\PermissionManagerInterface;

class Rbac implements RbacInterface
{
    public function __construct(
        private readonly PermissionManagerInterface $permissionManager,
        private readonly RoleManagerInterface $roleManager,
        private readonly ?RbacCacheService $cacheService = null
    ) {
    }

    public function hasPermission(string|int|PermissionInterface $permission, mixed $userId): bool
    {
        Assert::notEmpty($userId);

        // Check cache first
        if ($this->cacheService?->isEnabled()) {
            $cached = $this->cacheService->getPermission($permission, $userId);
            if ($cached !== null) {
                return $cached;
            }
        }

        $permissionId = $permission;
        if (is_object($permission)) {
            $permissionId = $permission->getId();
        } elseif (is_string($permission)) {
            $permissionId = $this->permissionManager->getPathId($permission);
        }

        $result = $this->permissionManager->hasPermission($permissionId, $userId);

        // Cache the result
        $this->cacheService?->setPermission($permission, $userId, $result);

        return $result;
    }

    public function hasRole(string|int|RoleInterface $role, mixed $userId): bool
    {
        Assert::notEmpty($userId);

        // Check cache first
        if ($this->cacheService?->isEnabled()) {
            $cached = $this->cacheService->getRole($role, $userId);
            if ($cached !== null) {
                return $cached;
            }
        }

        $roleId = $role;
        if (is_object($role)) {
            $roleId = $role->getId();
        } elseif (is_string($role)) {
            $roleId = $this->roleManager->getPathId($role);
        }

        $result = $this->roleManager->hasRole($roleId, $userId);

        // Cache the result
        $this->cacheService?->setRole($role, $userId, $result);

        return $result;
    }
}
