<?php

namespace PhpRbacBundle\Repository;

use Doctrine\ORM\ORMException;
use PhpRbacBundle\Entity\Permission;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use PhpRbacBundle\Core\Manager\NodeManagerInterface;
use PhpRbacBundle\Exception\RbacPermissionNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Permission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Permission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Permission[]    findAll()
 * @method Permission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PermissionRepository extends ServiceEntityRepository implements NestedSetInterface
{
    use NodeEntityTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Permission $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Permission $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Permission[] Returns an array of Permission objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Permission
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function getPathId(string $path) : int
    {
        return $this->pathId($path, RbacPermissionNotFoundException::class);
    }

    public function getById(int $nodeId) : Permission
    {
        $node = $this->find($nodeId);
        if (empty($node)) {
            throw new RbacPermissionNotFoundException();
        }

        return $node;
    }

    public function getPath(int $nodeId) : array
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
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(':nodeId', $nodeId);
        $result = $query->getResult();

        if (empty($result)) {
            throw new RbacPermissionNotFoundException();
        }

        return $result;
    }

    public function getChildren(int $nodeId) : array
    {
        $tableName = $this->getClassMetadata()
            ->getTableName();

        $sql = "
            SELECT
                node.*,
                (COUNT(parent.id)-1 - (sub_tree.innerDepth )) AS depth
            FROM
                {$tableName} as node,
                {$tableName} as parent,
                {$tableName} as sub_parent,
                (
                    SELECT
                        node.id,
                        (COUNT(parent.id) - 1) AS innerDepth
                    FROM
                        {$tableName} AS node,
                        {$tableName} AS parent
                    WHERE
                        node.left BETWEEN parent.left AND parent.right
                        AND (node.id = :nodeI)
                    GROUP BY
                        node.id
                    ORDER BY
                        node.left
                ) AS sub_tree
            WHERE
                node.left BETWEEN parent.left AND parent.right
                AND node.left BETWEEN sub_parent.left AND sub_parent.right
                AND sub_parent.id = sub_tree.id
            GROUP BY
                node.id
            HAVING
                depth = 1
            ORDER BY
                node.left
        ";

        $rsm = new ResultSetMapping;
        $rsm->addEntityResult($this->getClassName(), 'node');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(':nodeId', $nodeId);

        $result = $query->getResult();

        if (empty($result)) {
            throw new RbacPermissionNotFoundException();
        }

        return $result;
    }

    // public function deleteNode(int $nodeId) : bool
    // {
    //     $info = $this->getById($nodeId);

    //     $dql = "DELETE {$this->getClassName()} node WHERE node.left = :left";
    //     $query = $this->getEntityManager()->createQuery($dql);
    //     $query->setParameter(":left", $info->getLeft());
    //     $query->execute();

    //     $dql = "UPDATE {$this->getClassName()} node SET node.right = node.right - 1, node.left = node.left - 1 WHERE node.left BETWEEN :left AND :right";
    //     $query = $this->getEntityManager()->createQuery($dql);
    //     $query->setParameter(":left", $info->getLeft());
    //     $query->setParameter(":right", $info->getRight());
    //     $query->execute();

    //     $dql = "UPDATE {$this->getClassName()} node SET node.right = node.right - 2 WHERE node.right > :right";
    //     $query = $this->getEntityManager()->createQuery($dql);
    //     $query->setParameter(":right", $info->getRight());
    //     $query->execute();

    //     $dql = "UPDATE {$this->getClassName()} node SET node.left = node.left - 2 WHERE node.left > :right";
    //     $query = $this->getEntityManager()->createQuery($dql);
    //     $query->setParameter(":right", $info->getRight());
    //     $query->execute();

    //     return true;
    // }

    // public function deleteSubtree(int $nodeId) : bool
    // {
    //     $info = $this->getById($nodeId);
    //     $width = $info->getRight() - $info->getLeft() + 1;

    //     $dql = "DELETE {$this->getClassName()} node WHERE node.left BETWEEN :left AND :right";
    //     $query = $this->getEntityManager()->createQuery($dql);
    //     $query->setParameter(":left", $info->getLeft());
    //     $query->setParameter(":right", $info->getRight());
    //     $query->execute();

    //     $dql = "UPDATE {$this->getClassName()} node SET node.right = node.right - :widht WHERE node.right > :right";
    //     $query = $this->getEntityManager()->createQuery($dql);
    //     $query->setParameter(":width", $width);
    //     $query->setParameter(":right", $info->getRight());
    //     $query->execute();

    //     $dql = "UPDATE {$this->getClassName()} node SET node.left = node.left - :widht WHERE node.left > :right";
    //     $query = $this->getEntityManager()->createQuery($dql);
    //     $query->setParameter(":width", $width);
    //     $query->setParameter(":right", $info->getRight());
    //     $query->execute();

    //     return true;
    // }

    public function hasPermission(int $permissionId, mixed $userId) : bool
    {
        $pdo = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                COUNT(*) AS result
            FROM
                user_role
            INNER JOIN
                role AS TRdirect ON TRdirect.ID=user_role.role_id
            INNER JOIN
                role AS TR ON TR.`left` BETWEEN TRdirect.`left` AND TRdirect.`right`
            INNER JOIN
                (permission AS TPdirect
                    INNER JOIN
                        permission AS TP ON TPdirect.`left` BETWEEN TP.`left` AND TP.`right`
                    INNER JOIN
                        role_permission AS TRel ON TP.ID=TRel.permission_id
                ) ON TR.ID = TRel.role_id
            WHERE
                user_role.user_id = :userId
                AND TPdirect.id = :permissionId
        ";
        $query = $pdo->prepare($sql);
        $query->bindValue(":userId", $userId);
        $query->bindValue(":permissionId", $permissionId);
        $stmt = $query->executeQuery();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $row = $stmt->fetchAssociative();
        return $row['result'] >= 1;
    }

    public function addNode(string $title, string $description, int $parentId = NodeManagerInterface::ROOT_ID): Permission
    {
        return $this->updateForAdd($parentId, Permission::class, $title, $description);
    }
}
