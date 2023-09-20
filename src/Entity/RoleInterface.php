<?php

namespace PhpRbacBundle\Entity;

use Doctrine\Common\Collections\Collection;

interface RoleInterface extends NodeInterface
{
    public function getPermissions(): Collection;

    public function addPermission(PermissionInterface $permission): RoleInterface;

    public function removePermission(PermissionInterface $permission): RoleInterface;

    public function getParent(): ?RoleInterface;

    public function setParent(?RoleInterface $parent): RoleInterface;
}
