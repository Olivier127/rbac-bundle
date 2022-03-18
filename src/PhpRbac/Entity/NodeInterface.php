<?php

namespace PhpRbac\Entity;

interface NodeInterface
{
    public function getId(): int;

    public function getTitle(): string;

    public function getPath(): string;

    public function getDescription(): string;

    public function getParentId(): ?int;

    public function hasParent(): bool;
}
