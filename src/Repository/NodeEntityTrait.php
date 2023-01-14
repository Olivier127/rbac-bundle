<?php

namespace PhpRbacBundle\Repository;

use Exception;
use PhpRbacBundle\Entity\Node;
use PhpRbacBundle\Exception\RbacException;
use PhpRbacBundle\Core\Manager\NodeManagerInterface;

trait NodeEntityTrait
{
    public function deleteNode(int $nodeId): bool
    {
        if ($nodeId == NodeManagerInterface::ROOT_ID) {
            throw new RbacException("The Root Node cannot be deleted");
        }

        $entityManager = $this->getEntityManager();

        $info = $this->getById($nodeId);

        $dql = "DELETE {$this->getClassName()} node WHERE node.left = :left";
        $query = $entityManager->createQuery($dql);
        $query->setParameter(":left", $info->getLeft());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.right = node.right - 1, node.left = node.left - 1 WHERE node.left BETWEEN :left AND :right";
        $query = $entityManager->createQuery($dql);
        $query->setParameter(":left", $info->getLeft());
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.right = node.right - 2 WHERE node.right > :right";
        $query = $entityManager->createQuery($dql);
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.left = node.left - 2 WHERE node.left > :right";
        $query = $entityManager->createQuery($dql);
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        return true;
    }

    public function deleteSubtree(int $nodeId): bool
    {
        if ($nodeId == NodeManagerInterface::ROOT_ID) {
            throw new RbacException("The Root Node cannot be deleted");
        }

        $entityManager = $this->getEntityManager();

        $info = $this->getById($nodeId);
        $width = $info->getRight() - $info->getLeft() + 1;

        $dql = "DELETE {$this->getClassName()} node WHERE node.left BETWEEN :left AND :right";
        $query = $entityManager->createQuery($dql);
        $query->setParameter(":left", $info->getLeft());
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.right = node.right - :widht WHERE node.right > :right";
        $query = $entityManager->createQuery($dql);
        $query->setParameter(":width", $width);
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.left = node.left - :widht WHERE node.left > :right";
        $query = $entityManager->createQuery($dql);
        $query->setParameter(":width", $width);
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        return true;
    }

    public function pathId(string $path, string $classException): mixed
    {
        $pathCmpl = "root" . strtolower($path);

        $tableName = $this->getClassMetadata()
            ->getTableName();
        $parts = explode("/", $pathCmpl);
        $sql = "
            SELECT
                node.id,
                GROUP_CONCAT(parent.code ORDER BY parent.tree_left SEPARATOR '/') as path
            FROM
                {$tableName} as parent
            INNER JOIN
                {$tableName} as node ON node.tree_left BETWEEN parent.tree_left AND parent.tree_right
            WHERE
                node.code = :code
            GROUP BY
                node.id
            HAVING
                path = :path
        ";

        $pdo = $this->getEntityManager()
            ->getConnection();
        $query = $pdo->prepare($sql);
        $finalPart = end($parts);
        $query->bindValue(":code", strtolower($finalPart));
        $query->bindValue(":path", $pathCmpl);
        $result = $query->executeQuery();

        if ($result->rowCount() == 0) {
            throw new $classException($path);
        }

        $row = $result->fetchAssociative();
        return $row['id'];
    }

    public function reset()
    {
        $tableName = $this->getClassMetadata()
            ->getTableName();
        $sql = "DELETE FROM {$tableName} WHERE id > 1;";
        $sql .= "UPDATE {$tableName} SET tree_left = 0, tree_right = 1 WHERE id = 1;";
        $sql .= "ALTER TABLE {$tableName} AUTO_INCREMENT = 2;";
        $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql);
    }

    public function updateForAdd(int $parentId, string $nodeClass, string $code, string $description): Node
    {
        $entityManager = $this->getEntityManager();

        $parent = $this->getById($parentId);

        $dql = "UPDATE {$this->getClassName()} node SET node.right = node.right + 2 WHERE node.right >= :right";
        $query = $entityManager->createQuery($dql);
        $query->setParameter(":right", $parent->getRight());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.left = node.left + 2 WHERE node.left >= :right";
        $query = $entityManager->createQuery($dql);
        $query->setParameter(":right", $parent->getRight());
        $query->execute();

        $node = new $nodeClass();
        $node->setCode($code)
            ->setParent($parent)
            ->setDescription($description)
            ->setLeft($parent->getRight())
            ->setRight($parent->getRight() + 1);

        $this->add($node, true);

        $entityManager->refresh($parent);

        return $node;
    }

    private function getPathFunc(int $nodeId, string $rbacExceptionClass): array
    {
        $dql = "
            SELECT
                parent
            FROM
                {$this->getClassName()} parent
            JOIN
                {$this->getClassName()} node WITH node.left BETWEEN parent.left AND parent.right
            WHERE
                node.id = :nodeId
            ORDER BY
                parent.left
        ";

        $query = $this->getEntityManager()
            ->createQuery($dql);
        $query->setParameter(':nodeId', $nodeId);
        $result = $query->getResult();

        if (empty($result)) {
            throw new $rbacExceptionClass();
        }

        return $result;
    }
}
