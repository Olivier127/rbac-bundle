<?php

namespace PhpRbacBundle\Core;

use PhpRbacBundle\Entity\NodeInterface;

interface NodeManagerInterface
{
    /**
     * Root Id of the tree
     *
     * @var int
     */
    public const ROOT_ID = 1;

    /**
     * Return the id of the root of the tree
     *
     * @return int
     */
    public function rootId(): int;

    /**
     * Adds a new role or permission
     *
     * @param string  $title
     * @param string  $description
     * @param int     $parentId     default = 1
     *
     * @return NodeInterface
     */
    public function add(string $title, string $description, int $parentId = self::ROOT_ID): NodeInterface;

    /**
     * Adds a new path of roles or permissions
     * Create the chain of node with the array of description attache to each node
     *
     * The path must be like /node1/node2/node3/node4 etc..
     * This method create only the missings subnodes with the descriptions
     *
     * @param string         $path         the path must be like /node1/node2/node3/node4
     * @param array<string>  $descriptions Description must be like ["node3", "node4"] if node1 and node2 exist
     *
     * @return NodeInterface The last node
     */
    public function addPath(string $path, array $descriptions): NodeInterface;

    /**
     * Return the node associate to the id
     *
     * @param int $nodeId Node Id
     *
     * @throws RbacException
     * @return NodeInterface
     */
    public function getNode(int $nodeId): NodeInterface;

    /**
     * Update Node
     *
     * @param int    $nodeId
     * @param string $title       New title
     * @param string $description New description
     *
     * @throws RbacException
     * @return NodeInterface The updated Node
     */
    public function updateNode(int $nodeId, string $title, string $description): NodeInterface;

    /**
     * Get the child of the node
     *
     * @param int $nodeId
     *
     * @throws RbacException
     * @return NodeInterface[] The updated Node
     */
    public function getChildren(int $nodeId): array;

    /**
     * Get all the parents of the node
     *
     * @param int $nodeId
     *
     * @throws RbacException
     * @return NodeInterface[] The updated Node
     */
    public function getParents(int $nodeId): array;


    /**
     * Get the depth of the node
     *
     * @param int $nodeId
     *
     * @throws RbacException
     * @return int
     */
    public function getDepth(int $nodeId): int;

    /**
     * Return the id of the last node of the path
     *
     * @todo this has a limit of 1000 characters on $path
     *
     * @param string $path such as /role1/role2/role3 ( a single slash is root)
     *
     * @throws RbacException
     * @return int
     */
    public function getPathId(string $path) : int;

    /**
     * Return the path of a node
     *
     * @param int $nodeId
     *
     * @throws RbacException
     * @return string
     */
    public function getPath(int $nodeId) : string;

    /**
     * Return the parent of a node
     *
     * @param int $nodeId
     *
     * @return NodeInterface
     */
    public function getParent(int $nodeId) : NodeInterface;
}
