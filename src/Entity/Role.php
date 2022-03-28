<?php

namespace PhpRbacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use PhpRbacBundle\Repository\RoleRepository;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Index(name:"role_idx", columns: ["title", "left", "right"])]
class Role extends Node implements RoleInterface
{
    #[ORM\ManyToMany(targetEntity: Permission::class, cascade:['persist', 'remove', 'refresh'])]
    #[ORM\JoinTable(name: "role_permission")]
    #[ORM\JoinColumn(name: "role_id", referencedColumnName: "id", onDelete: "cascade")]
    #[ORM\InverseJoinColumn(name: "permission_id", referencedColumnName: "id", onDelete: "cascade")]
    private $permissions;

    public function __construct()
    {
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setId(int $id) : self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getCollectionPermissions() : Collection
    {
        return $this->permissions;
    }

    public function getPermissions() : array
    {
        return $this->getCollectionPermissions()->toArray();
    }


    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        $this->permissions->removeElement($permission);

        return $this;
    }

    public function setPermissions(?Collection $permissions) : self
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function __toString()
    {
        return $this->getTitle();
    }
}
