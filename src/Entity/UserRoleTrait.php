<?php

namespace PhpRbacBundle\Entity;

use PhpRbacBundle\Repository\UserRoleRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRoleRepository::class)]

trait UserRoleTrait
{
    #[ORM\ManyToMany(targetEntity: Role::class, cascade:['persist', 'remove', 'refresh'])]
    #[ORM\JoinTable(name: "user_role")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "cascade")]
    #[ORM\InverseJoinColumn(name: "role_id", referencedColumnName: "id", onDelete: "cascade")]
    private $rbacRoles;

    /**
     * @return Collection<int, Role>
     */
    public function getRbacRoles(): Collection
    {
        return $this->rbacRoles;
    }

    public function addRbacRole(Role $role): self
    {
        if (!$this->rbacRoles->contains($role)) {
            $this->rbacRoles[] = $role;
        }

        return $this;
    }

    public function removeRbacRole(Role $role): self
    {
        $this->rbacRoles->removeElement($role);

        return $this;
    }
}
