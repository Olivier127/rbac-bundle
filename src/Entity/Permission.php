<?php

namespace PhpRbacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class Permission extends Node implements PermissionInterface
{
    #[ORM\ManyToOne(targetEntity: PermissionInterface::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete:"cascade")]
    protected ?PermissionInterface $parent = null;

    public function getParent(): PermissionInterface
    {
        return $this->parent;
    }

    public function setParent(PermissionInterface $parent): PermissionInterface
    {
        $this->parent = $parent;

        return $this;
    }
}
