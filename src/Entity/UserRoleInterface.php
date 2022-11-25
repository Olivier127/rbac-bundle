<?php

namespace PhpRbacBundle\Entity;

use Doctrine\Common\Collections\Collection;

interface UserRoleInterface
{
    public function getRbacRoles(): Collection;

    public function addRbacRole(Role $role): void;

    public function removeRbacRole(Role $role): void;
}
