<?php

namespace PhpRbacBundle\Entity;

interface NodeInterface
{
    public function getId(): ?int;

    public function getCode(): ?string;

    public function setCode(string $code): static;

    public function getDescription(): string;

    public function setDescription(string $description): static;

    public function getLeft(): int;

    public function setLeft(int $left): static;

    public function getRight(): int;

    public function setRight(int $right): static;
}
