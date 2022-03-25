<?php

namespace PhpRbacBundle\Entity;

use PhpRbacBundle\Repository\PermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Index(name:"permission_idx", columns: ["title", "left", "right"])]
class Permission extends Node implements PermissionInterface
{
    public function setId(int $id) : self
    {
        $this->id = $id;

        return $this;
    }
}
