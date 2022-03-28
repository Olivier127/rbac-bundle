<?php

namespace PhpRbacBundle\Repository;

use PhpRbacBundle\Entity\Node;
use PhpRbacBundle\Exception\RbacException;
use PhpRbacBundle\Core\Manager\NodeManagerInterface;

trait NodeEntityTrait
{
    public function deleteNode(int $nodeId) : bool
    {
        if ($nodeId == NodeManagerInterface::ROOT_ID) {
            throw new RbacException("The Root Node cannot be deleted");
        }
        $info = $this->getById($nodeId);

        $dql = "DELETE {$this->getClassName()} node WHERE node.left = :left";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(":left", $info->getLeft());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.right = node.right - 1, node.left = node.left - 1 WHERE node.left BETWEEN :left AND :right";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(":left", $info->getLeft());
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.right = node.right - 2 WHERE node.right > :right";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.left = node.left - 2 WHERE node.left > :right";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        return true;
    }

    public function deleteSubtree(int $nodeId) : bool
    {
        if ($nodeId == NodeManagerInterface::ROOT_ID) {
            throw new RbacException("The Root Node cannot be deleted");
        }

        $info = $this->getById($nodeId);
        $width = $info->getRight() - $info->getLeft() + 1;

        $dql = "DELETE {$this->getClassName()} node WHERE node.left BETWEEN :left AND :right";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(":left", $info->getLeft());
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.right = node.right - :widht WHERE node.right > :right";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(":width", $width);
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.left = node.left - :widht WHERE node.left > :right";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(":width", $width);
        $query->setParameter(":right", $info->getRight());
        $query->execute();

        return true;
    }

    public function pathId(string $path, string $classException) : mixed
    {
        $path = "root".strtolower($path);
        $tableName = $this->getClassMetadata()
            ->getTableName();
        $parts = explode("/", $path);
        $sql = "
            SELECT
                node.id,
                GROUP_CONCAT(parent.title ORDER BY parent.left SEPARATOR '/') as path
            FROM
                {$tableName} as parent
            INNER JOIN
                {$tableName} as node ON node.left BETWEEN parent.left AND parent.right
            WHERE
                node.title = :title
            GROUP BY
                node.id
            HAVING
                path = :path
        ";
        $pdo = $this->getEntityManager()->getConnection();
        $query = $pdo->prepare($sql);
        $finalPart = end($parts);
        $query->bindValue(":title", strtolower($finalPart));
        $query->bindValue(":path", $path);
        $result = $query->executeQuery();

        if ($result->rowCount() == 0) {
            throw new $classException($path);
        }

        $row = $result->fetchAssociative();
        return $row['id'];
    }

    public function reset()
    {
        $tableName = $this->getClassMetadata()->getTableName();
        $sql = "DELETE FROM {$tableName} WHERE id > 1;";
        $sql .= "UPDATE {$tableName} SET `left` = 0, `right` = 1 WHERE id = 1;";
        $sql .= "ALTER TABLE {$tableName} AUTO_INCREMENT = 2;";
        $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql);
    }

    public function updateForAdd(int $parentId, string $nodeClass, string $title, string $description) : Node
    {
        $parent = $this->getById($parentId);

        $dql = "UPDATE {$this->getClassName()} node SET node.right = node.right + 2 WHERE node.right >= :right";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(":right", $parent->getRight());
        $query->execute();

        $dql = "UPDATE {$this->getClassName()} node SET node.left = node.left + 2 WHERE node.left >= :right";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(":right", $parent->getRight());
        $query->execute();

        $node = new $nodeClass;
        $node->setTitle($title)
            ->setDescription($description)
            ->setLeft($parent->getRight())
            ->setRight($parent->getRight() + 1);

        $this->add($node, true);

        $this->getEntityManager()->refresh($parent);

        return $node;
    }
}
