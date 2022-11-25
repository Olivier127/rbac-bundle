<?php

namespace PhpRbacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
#[ORM\UniqueConstraint('unique_code', ['code', 'parent_id'])]
#[ORM\Index(name:"permission_idx", columns: ["code", "tree_left", "tree_right"])]
abstract class Node implements NodeInterface
{
    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $code;

    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $description;

    #[ORM\Column(name:'tree_left', type: 'integer')]
    protected ?int $left;

    #[ORM\Column(name:'tree_right', type: 'integer')]
    protected ?int $right;

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLeft(): int
    {
        return $this->left;
    }

    public function setLeft(int $left): static
    {
        $this->left = $left;

        return $this;
    }

    public function getRight(): int
    {
        return $this->right;
    }

    public function setRight(int $right): static
    {
        $this->right = $right;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function __toString() : string
    {
        return $this->getCode();
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

}
