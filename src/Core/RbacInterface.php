<?php

namespace PhpRbacBundle\Core;

use PhpRbacBundle\Entity\PermissionInterface;
use PhpRbacBundle\Entity\RoleInterface;

interface RbacInterface
{
    /**
     * Checks whether a user has a permission or not.
     *
     * @param string|int|PermissionInterface $permission you can provide a path like /some/permission, a code,
     *                                                   the permission ID or the object PermissionInterface
     * @param mixed                          $userId
     *
     * @throws RbacPermissionNotFoundException
     * @throws RbacUserNotProvidedException
     * @return bool
     */
    public function hasPermission(string|int|PermissionInterface $permission, mixed $userId): bool;

/**
     * Test the role to a user
     *
     * @param string|int|RoleInterface $role   you can provide a path like /some/role, a code,
     *                                         the role Id or the object RoleInterface
     * @param mixed                    $userId
     *
     * @throws RbacUserNotProvidedException
     * @return bool
     */
    public function hasRole(string|int|RoleInterface $role, mixed $userId): bool;
}
