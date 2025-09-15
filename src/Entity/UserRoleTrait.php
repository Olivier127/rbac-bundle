<?php

namespace PhpRbacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use PhpRbacBundle\Repository\UserRoleRepository;

#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
trait UserRoleTrait
{
    #[ORM\ManyToMany(targetEntity: RoleInterface::class)]
    #[ORM\JoinTable(name: "user_role")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "role_id", referencedColumnName: "id")]
    private Collection $rbacRoles;

    public function __construct()
    {
        $this->rbacRoles = new ArrayCollection();
    }

    /**
     * @return Collection<int, RoleInterface>
     */
    public function getRbacRoles(): Collection
    {
        return $this->rbacRoles;
    }

    public function addRbacRole(RoleInterface $role): void
    {
        if (!$this->rbacRoles->contains($role)) {
            $this->rbacRoles[] = $role;
        }
    }

    public function removeRbacRole(RoleInterface $role): void
    {
        $this->rbacRoles->removeElement($role);
    }
}
