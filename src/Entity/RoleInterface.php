<?php

namespace PhpRbacBundle\Entity;

interface RoleInterface extends NodeInterface
{
    /**
     * Get All the permissions assign to the role
     *
     * @return PermissionInterface[]
     */
    public function getPermissions(): array;

    public function addPermission(Permission $permission): self;

    public function removePermission(Permission $permission): self;
}
