<?php

namespace PhpRbacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\MappedSuperclass]
abstract class Role extends Node implements RoleInterface
{
    #[ORM\ManyToMany(targetEntity: PermissionInterface::class, cascade:['persist', 'remove', 'refresh'])]
    #[ORM\JoinTable(name: "role_permission")]
    #[ORM\JoinColumn(name: "role_id", referencedColumnName: "id", onDelete: "cascade")]
    #[ORM\InverseJoinColumn(name: "permission_id", referencedColumnName: "id", onDelete: "cascade")]
    private Collection $permissions;

    #[ORM\ManyToOne(targetEntity: RoleInterface::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete:"cascade")]
    protected ?RoleInterface $parent = null;

    public function __construct()
    {
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(PermissionInterface $permission): RoleInterface
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }

        return $this;
    }

    public function removePermission(PermissionInterface $permission): RoleInterface
    {
        $this->permissions->removeElement($permission);

        return $this;
    }

    public function setPermissions(?Collection $permissions): RoleInterface
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function getParent(): ?RoleInterface
    {
        return $this->parent;
    }

    public function setParent(?RoleInterface $parent): RoleInterface
    {
        $this->parent = $parent;

        return $this;
    }
}
