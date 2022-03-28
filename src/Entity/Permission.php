<?php

namespace PhpRbacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PhpRbacBundle\Repository\PermissionRepository;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Index(name:"permission_idx", columns: ["title", "left", "right"])]
class Permission extends Node implements PermissionInterface
{
    public function setId(int $id) : self
    {
        $this->id = $id;

        return $this;
    }

    public function __toString()
    {
        return $this->getTitle();
    }
}
