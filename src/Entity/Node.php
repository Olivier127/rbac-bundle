<?php

namespace PhpRbacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Node implements NodeInterface
{
    /**
     * @var ?int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $title;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $description;

    /**
     * @var int
     */
    #[ORM\Column(name:'`left`', type: 'integer')]
    protected $left;

    /**
     * @var int
     */
    #[ORM\Column(name:'`right`', type: 'integer')]
    protected $right;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setTitle(string $title) : static
    {
        $this->title = strtolower($title);

        return $this;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setDescription(string $description) : static
    {
        $this->description = $description;

        return $this;
    }

    public function getLeft() : int
    {
        return $this->left;
    }

    public function setLeft(int $left) : static
    {
        $this->left = $left;

        return $this;
    }

    public function getRight() : int
    {
        return $this->right;
    }

    public function setRight(int $right) : static
    {
        $this->right = $right;

        return $this;
    }
}
