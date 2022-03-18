<?php

namespace PhpRbac\Core;

use PhpRbac\Entity\NodeInterface;

interface NodeManagementInterface
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
     * @param string      $title
     * @param string      $description
     * @param string|null $parentId
     *
     * @return PermissionInterface|RoleInterface
     */
    public function add(string $title, string $description, string $parentId = null): NodeInterface;

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
     * @return PermissionInterface|RoleInterface The last node
     */
    public function addPath(string $path, array $description): NodeInterface;

    /**
     * Return the id of the path or the title
     *
     * @param string $entity path or title
     *
     * @throws RbacPermissionNotFoundException|RbacRoleNotFoundException
     * @return int Node Id
     */
    public function returnId(string $entity): int;

    /**
     * Return the node associate to the id
     *
     * @param int $nodeId Node Id
     *
     * @throws RbacPermissionNotFoundException|RbacRoleNotFoundException
     * @return PermissionInterface|RoleInterface
     */
    public function getNode(int $nodeId): NodeInterface;

    /**
     * Update Node
     *
     * @param int    $nodeId
     * @param string $title       New title
     * @param string $description New description
     *
     * @throws RbacPermissionNotFoundException|RbacRoleNotFoundException
     * @return PermissionInterface|RoleInterface The updated Node
     */
    public function updateNode(int $nodeId, string $title, string $description): NodeInterface;

    /**
     * Get the child of the node
     *
     * @param int $nodeId
     *
     * @throws RbacPermissionNotFoundException|RbacRoleNotFoundException
     * @return PermissionInterface|RoleInterface The updated Node
     */
    public function getChildren(int $nodeId): array;

    /**
     * Get all the parents of the node
     *
     * @param int $nodeId
     *
     * @throws RbacPermissionNotFoundException|RbacRoleNotFoundException
     * @return PermissionInterface|RoleInterface The updated Node
     */
    public function getParents(int $nodeId): array;


    /**
     * Get the depth of the node
     *
     * @param int $nodeId
     *
     * @throws RbacPermissionNotFoundException|RbacRoleNotFoundException
     * @return int
     */
    public function getDepth(int $nodeId): int;
}
