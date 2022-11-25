<?php

namespace PhpRbacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PhpRbacBundle\Repository\PermissionRepository;

#[ORM\MappedSuperclass]
class Permission extends Node implements PermissionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id;

    #[ORM\OneToOne(targetEntity: PermissionInterface::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete:"cascade")]
    protected ?PermissionInterface $parent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

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
