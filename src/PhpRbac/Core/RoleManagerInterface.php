<?php

namespace PhpRbac\Core;

use PhpRbac\Entity\RoleInterface;
use PhpRbac\Entity\PermissionInterface;
use PhpRbac\Exception\RbacRoleNotFoundException;

interface RoleManagerInterface extends NodeManagementInterface
{
    /**
     * Remove role from system and attach all the sub-roles to the parent
     *
     * @param RoleInterface $role
     *
     * @throws RbacRoleNotFoundException
     * @return bool
     */
    public function remove(RoleInterface $role): bool;

    /**
     * Remove role and all the sub-roles from system
     *
     * @param RoleInterface $role
     *
     * @throws RbacRoleNotFoundException
     * @return bool
     */
    public function removeRecursively(RoleInterface $role): bool;

    /**
     * Unassigns all permissions belonging to a role
     *
     * @param RoleInterface $role
     *
     * @return bool
     */
    public function unassignPermissions(RoleInterface $role): bool;

    /**
     * Unassign all users that have a certain role
     *
     * @param RoleInterface $role
     *
     * @return bool
     */
    public function unassignUsers(RoleInterface $role): bool;

    /**
     * Checks to see if a role has a permission or not
     *
     * @param RoleInterface        $role
     * @param PermissionInterface $permission
     *
     * @return bool
     */
    public function hasPermission(RoleInterface $role, PermissionInterface $permission): bool;

    /**
     * Returns all permissions assigned to a role
     *
     * @param RoleInterface $role
     *
     * @throws RbacRoleNotFoundException
     * @return PermissionInterface[]
     */
    public function permissions(RoleInterface $role): array;

    /**
     * Assign a role to a permission.
     * Alias for what's in the base class
     *
     * @param RoleInterface       $role
     * @param PermissionInterface $permission
     *
     * @throws RbacPermissionNotFoundException
     * @throws RbacUserNotProvidedException
     * @return bool
     */
    public function assignPermission(RoleInterface $role, PermissionInterface $permission): bool;

    /**
     * Check if a user has the role
     *
     * @param int   $roleId
     * @param mixed $userId
     *
     * @return bool
     */
    public function hasRole(int $roleId, mixed $userId): bool;
}
