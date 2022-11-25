<?php

namespace PhpRbacBundle\Repository;

use PhpRbacBundle\Entity\NodeInterface;
use PhpRbacBundle\Exception\RbacException;
use PhpRbacBundle\Core\Manager\NodeManagerInterface;

interface NestedSetInterface
{
    /**
     * Get the id of the last node of the path
     *
     * @param string $path
     *
     * @throws RbacException Not Found
     * @return int
     */
    public function getPathId(string $path): int;

    /**
     * Return the node by id
     *
     * @param int $nodeId
     *
     * @throws RbacException Not Found
     * @return NodeInterface
     */
    public function getById(int $nodeId): NodeInterface;

    /**
     * Return the nodes of the path
     *
     * @param int $nodeId
     *
     * @throws RbacException Not Found
     * @return NodeInterface[]
     */
    public function getPath(int $nodeId): array;

    /**
     * Return the immediat children of a node
     *
     * @param int $nodeId
     *
     * @throws RbacException Not Found
     * @return NodeInterface[]
     */
    public function getChildren(int $nodeId): array;

    /**
     * Deletes a node and shifts the children up
     *
     * @param int $nodeId
     *
     * @return bool
     */
    public function deleteNode(int $nodeId): bool;

    /**
     * Deletes a node and all its descendants
     *
     * @param int $nodeId
     *
     * @return bool
     */
    public function deleteSubtree(int $nodeId): bool;

    /**
     * Add a node in the tree
     *
     * @param string $code
     * @param string $description
     * @param int    $parentId
     *
     * @return void
     */
    public function addNode(string $code, string $description, int $parentId = NodeManagerInterface::ROOT_ID): NodeInterface;
}
