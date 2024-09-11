<?php

namespace PhpRbacBundle\Core\Manager;

use PhpRbacBundle\Entity\NodeInterface;
use PhpRbacBundle\Exception\RbacException;
use PhpRbacBundle\Repository\RoleRepository;
use PhpRbacBundle\Repository\PermissionRepository;

abstract class NodeManager implements NodeManagerInterface
{
    public function __construct(protected RoleRepository|PermissionRepository $repository)
    {
    }

    public function rootId(): int
    {
        return self::ROOT_ID;
    }

    public function add(string $code, string $description, int $parentId = self::ROOT_ID): NodeInterface
    {
        try {
            $parentPath = $parentId == self::ROOT_ID ? "" : $this->getPath($parentId);
            $id = $this->getPathId($parentPath . "/" . $code);
            return $this->getNode($id);
        } catch (RbacException) {
            return $this->repository->addNode($code, $description, $parentId);
        }
    }

    public function addPath(string $path, array $descriptions): NodeInterface
    {
        if ($path[0] !== '/') {
            throw new \Exception("The path supplied is not valid.");
        }

        $path = substr($path, 1);
        $parts = explode("/", $path);
        $parentId = self::ROOT_ID;
        $index = 0;
        $currentPath = "";
        $nodesCreated = 0;
        $node = null;

        foreach ($parts as $part) {
            $description = array_key_exists(strtolower($part), $descriptions) ? $descriptions[$part] : "";

            $currentPath .= "/" . $part;
            try {
                $pathId = $this->getPathId($currentPath);
                $parentId = $pathId;
                $node = $this->getNode($pathId);
            } catch (RbacException) {
                $node = $this->add($part, $description, $parentId);
                $parentId = $node->getId();
                $nodesCreated++;
            }
            $index++;
        }

        return $node;
    }

    public function getPathId(string $path): int
    {
        if (substr($path, -1) == "/") {
            $path = substr($path, 0, strlen($path) - 1);
        }
        return $this->repository->getPathId($path);
    }

    public function getNode(int $nodeId): NodeInterface
    {
        return $this->repository->getById($nodeId);
    }

    public function updateNode(int $nodeId, string $code, string $description): NodeInterface
    {
        $node = $this->getNode($nodeId);
        $node->setCode($code);
        $node->setDescription($description);

        $this->repository->add($node, true);

        return $node;
    }

    public function getPath(int $nodeId): string
    {
        $nodes = $this->repository->getPath($nodeId);
        $path = "";
        if ($nodeId == self::ROOT_ID) {
            return "/";
        }
        foreach ($nodes as $node) {
            if ($node->getId() != self::ROOT_ID) {
                $path .= "/" . $node->getCode();
            }
        }

        return $path;
    }

    public function getChildren(int $nodeId): array
    {
        return $this->repository->getChildren($nodeId);
    }

    public function getParents(int $nodeId): array
    {
        return $this->repository->getPath($nodeId);
    }

    public function getDepth(int $nodeId): int
    {
        return count($this->repository->getPath($nodeId));
    }

    public function getParent(int $nodeId): NodeInterface
    {
        $nodes = $this->repository->getPath($nodeId);
        if ($nodeId == self::ROOT_ID) {
            throw new RbacException("no parent for this node");
        }

        return $nodes[count($nodes) - 2];
    }

    public function reset()
    {
        return $this->repository->reset();
    }
}
