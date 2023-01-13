<?php

namespace PhpRbacBundle\Core\Manager;

use PhpRbacBundle\Entity\RoleInterface;

interface UserManagerInterface
{
    /**
     * Test the role to a user
     *
     * @param string|integer $role   Id, path or code of the role to test
     * @param mixed          $userId
     *
     * @throws RbacUserNotProvidedException
     * @return bool
     */
    public function hasRole(string|int $role, mixed $userId): bool;

    /**
     * Assigns a role to a user
     *
     * @param RoleInterface $role
     * @param mixed         $userId
     *
     * @throws RbacUserNotProvidedException
     * @return boolean
     */
    public function assign(RoleInterface $role, mixed $userId): bool;

    /**
     * Unassigns a role from a user
     *
     * @param RoleInterface $role
     * @param mixed         $userId
     *
     * @throws RbacUserNotProvidedException
     * @return boolean
     */
    public function unassign(RoleInterface $role, mixed $userId): bool;

    /**
     * Returns all roles of a user
     *
     * @param mixed $userId
     *
     * @throws RbacUserNotProvidedException
     * @return RoleInterface[]
     *
     */
    public function allRoles(mixed $userId): array;

    /**
     * Return count of roles assigned to a user
     *
     * @param mixed $userId
     *
     * @throws RbacUserNotProvidedException User Not found
     * @return int                          Count of Roles assigned to a User
     */
    public function roleCount(mixed $userId): int;

    /**
     * Remove all role-user relations
     *
     * @return boolean
     */
    public function resetAssignments(): bool;
}
