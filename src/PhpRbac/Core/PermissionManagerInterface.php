<?php

namespace PhpRbac\Core;

use PhpRbac\Entity\RoleInterface;
use PhpRbac\Entity\PermissionInterface;
use PhpRbac\Exception\RbacPermissionNotFoundException;

interface PermissionManagerInterface extends NodeManagementInterface
{
    /**
     * Remove permission and attach all the sub-permission to the parent
     *
     * @param PermissionInterface $permission
     *
     * @throws RbacPermissionNotFoundException
     * @return boolean
     */
    public function remove(PermissionInterface $permission): bool;

    /**
     * Remove Permission and all sub-permissions from system
     *
     * @param PermissionInterface $permission
     *
     * @throws RbacPermissionNotFoundException
     * @return boolean
     */
    public function removeRecursively(PermissionInterface $permission): bool;

    /**
     * Unassignes all roles of this permission, and returns their number
     *
     * @param PermissionInterface $permission
     *
     * @throws RbacPermissionNotFoundException
     * @return bool
     */
    public function unassignRoles(PermissionInterface $permission): bool;

    /**
     * Returns all roles assigned to a permission
     *
     * @param PermissionInterface $permission
     *
     * @throws RbacPermissionNotFoundException
     * @return RoleInterface[]
     */
    public function roles(PermissionInterface $permission): array;

    /**
     * check if a user has the permission or not
     *
     * @param int   $permissionId
     * @param mixed $userId
     *
     * @return bool
     */
    public function hasPermission(int $permissionId, mixed $userId): bool;
}
