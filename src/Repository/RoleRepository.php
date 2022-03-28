<?php

namespace PhpRbacBundle\Repository;

use Doctrine\ORM\ORMException;
use PhpRbacBundle\Entity\Role;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use PhpRbacBundle\Core\Manager\NodeManagerInterface;
use PhpRbacBundle\Exception\RbacRoleNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository implements NestedSetInterface
{
    use NodeEntityTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Role $entity, bool $flush = true): void
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
    public function remove(Role $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Role[] Returns an array of Role objects
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
    public function findOneBySomeField($value): ?Role
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getPathId(string $path,) : int
    {
        return $this->pathId($path, RbacRoleNotFoundException::class);
    }

    public function getById(int $nodeId) : Role
    {
        $node = $this->find($nodeId);
        if (empty($node)) {
            throw new RbacRoleNotFoundException();
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
            throw new RbacRoleNotFoundException();
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
            throw new RbacRoleNotFoundException();
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

    public function deletePermissions(Role $role) : Role
    {
        $role->setPermissions(null);
        $this->add($role, true);

        return $role;
    }

    public function hasPermission(int $roleId, int $permissionId) : bool
    {
        $pdo = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                COUNT(*) AS result
                FROM role_permission
                INNER JOIN permission ON permission.id = role_permission.permission_id
                INNER JOIN role ON role.id = role_permission.role_id
            WHERE
                role.`left` BETWEEN
                    (SELECT `left` FROM role WHERE ID = :roleId)
                    AND
                    (SELECT `right` FROM role WHERE ID = :roleId)
                AND
                    permission.id IN (
                        SELECT
                            parent.id
                        FROM
                            permission AS node,
                            permission AS parent
                        WHERE
                            node.`left` BETWEEN parent.`left` AND parent.`right`
                            AND node.ID= :permissionId
                        ORDER BY parent.`left`
                    )
        ";
        $query = $pdo->prepare($sql);
        $query->bindValue(":roleId", $roleId);
        $query->bindValue(":permissionId", $permissionId);
        $stmt = $query->executeQuery();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $row = $stmt->fetchAssociative();
        return $row['result'] >= 1;
    }

    public function hasRole(int $roleId, mixed $userId) : bool
    {
        $pdo = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT
                COUNT(*) as result
            FROM
                user_role
            INNER JOIN role AS TRdirect ON (TRdirect.id=user_role.role_id)
            INNER JOIN role AS TR ON (TR.`left` BETWEEN TRdirect.`left` AND TRdirect.`right`)
            WHERE
                user_role.user_id = :userId AND TR.ID = :roleId
        ";
        $query = $pdo->prepare($sql);
        $query->bindValue(":roleId", $roleId);
        $query->bindValue(":userId", $userId);
        $stmt = $query->executeQuery();
        if ($stmt->rowCount() == 0) {
            return false;
        }

        $row = $stmt->fetchAssociative();
        return $row['result'] >= 1;
    }

    public function addNode(string $title, string $description, int $parentId = NodeManagerInterface::ROOT_ID): Role
    {
        return $this->updateForAdd($parentId, Role::class, $title, $description);
    }
}
