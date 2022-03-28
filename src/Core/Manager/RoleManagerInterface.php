<?php

namespace PhpRbacBundle\Core\Manager;

use PhpRbacBundle\Entity\RoleInterface;
use PhpRbacBundle\Exception\RbacRoleNotFoundException;

interface RoleManagerInterface extends NodeManagerInterface
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
     * Add a permission to a role
     *
     * @param RoleInterface $role
     * @param string        $permission example : /permission1/permission2
     *
     * @return void
     */
    public function assignPermission(RoleInterface $role, string $permission);

    /**
     * Remove a permission to a role
     *
     * @param RoleInterface $role
     * @param string        $permission example : /permission1/permission2
     *
     * @return void
     */
    public function unassignPermission(RoleInterface $role, string $permission);

    /**
     * Unassigns all permissions belonging to a role
     *
     * @param RoleInterface $role
     *
     * @return bool
     */
    public function unassignPermissions(RoleInterface $role): bool;

    /**
     * Checks to see if a role has a permission or not
     *
     * @param int $roleId
     * @param int $permissionId
     *
     * @return bool
     */
    public function hasPermission(int $roleId, int $permissionId): bool;

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
