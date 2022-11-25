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
use PhpRbacBundle\Entity\NodeInterface;
use PhpRbacBundle\Entity\PermissionInterface;
use PhpRbacBundle\Entity\RoleInterface;

/**
 * @method Permission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Permission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Permission[]    findAll()
 * @method Permission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PermissionRepository extends ServiceEntityRepository implements NestedSetInterface
{
    use NodeEntityTrait;

    private string $roleTableName;

    private string $tableName;

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);

        $this->roleTableName = $this->getEntityManager()
            ->getClassMetadata(RoleInterface::class)
            ->getTableName();
        $this->tableName = $this->getClassMetadata()
            ->getTableName();
    }

    public function initTable()
    {
        $sql = "SET FOREIGN_KEY_CHECKS = 0; TRUNCATE role_permission; TRUNCATE {$this->tableName}; SET FOREIGN_KEY_CHECKS = 1";
        $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql);
        $sql = "INSERT INTO {$this->tableName} (id, code, description, tree_left, tree_right) VALUES (1, 'root', 'root', 0, 1)";
        $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql);
    }


    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Permission $entity, bool $flush = true): void
    {
        $this->getEntityManager()
            ->persist($entity);
        if ($flush) {
            $this->getEntityManager()
                ->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Permission $entity, bool $flush = true): void
    {
        $this->getEntityManager()
            ->remove($entity);
        if ($flush) {
            $this->getEntityManager()
                ->flush();
        }
    }

    public function getPathId(string $path): int
    {
        return $this->pathId($path, RbacPermissionNotFoundException::class);
    }

    public function getById(int $nodeId): Permission
    {
        $node = $this->find($nodeId);
        if (empty($node)) {
            throw new RbacPermissionNotFoundException("Permission {$nodeId} not found");
        }

        return $node;
    }

    public function getPath(int $nodeId): array
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
            throw new RbacPermissionNotFoundException();
        }

        return $result;
    }

    public function getChildren(int $nodeId): array
    {
        $sql = "
            SELECT
                node.*,
                (COUNT(parent.id)-1 - (sub_tree.innerDepth )) AS depth
            FROM
                {$this->tableName} as node,
                {$this->tableName} as parent,
                {$this->tableName} as sub_parent,
                (
                    SELECT
                        node.id,
                        (COUNT(parent.id) - 1) AS innerDepth
                    FROM
                        {$this->tableName} AS node,
                        {$this->tableName} AS parent
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

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult($this->getClassName(), 'node');
        $query = $this->getEntityManager()
            ->createNativeQuery($sql, $rsm);
        $query->setParameter(':nodeId', $nodeId);

        $result = $query->getResult();

        if (empty($result)) {
            throw new RbacPermissionNotFoundException();
        }

        return $result;
    }

    public function hasPermission(int $permissionId, mixed $userId): bool
    {
        $pdo = $this->getEntityManager()
            ->getConnection();

        $sql = "
            SELECT
                COUNT(*) AS result
            FROM
                user_role
            INNER JOIN
                {$this->roleTableName} AS TRdirect ON TRdirect.ID=user_role.role_id
            INNER JOIN
                {$this->roleTableName} AS TR ON TR.tree_left BETWEEN TRdirect.tree_left AND TRdirect.tree_right
            INNER JOIN
                ({$this->tableName} AS TPdirect
                    INNER JOIN
                    {$this->tableName} AS TP ON TPdirect.tree_left BETWEEN TP.tree_left AND TP.tree_right
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

    public function addNode(string $code, string $description, int $parentId = NodeManagerInterface::ROOT_ID): PermissionInterface
    {
        /** @var PermissionInterface $node */
        $node = $this->updateForAdd($parentId, $this->getClassName(), $code, $description);

        return $node;
    }
}
