<?php

namespace Test\PhpRbacBundle\Permission;

use Exception;
use PhpRbacBundle\Core\Manager\RoleManager;
use PhpRbacBundle\Core\Manager\PermissionManager;
use PhpRbacBundle\Exception\RbacPermissionNotFoundException;
use PhpRbacBundle\Repository\PermissionRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TestPermission
{
    protected static $kernel;

    protected $container;

    protected function setUp(): void
    {
        self::$kernel = self::createKernel();
        self::$kernel->boot();
        $this->container = self::$kernel->getContainer();

        $this->container->get(PermissionRepository::class)->initTable();
        $this->container->get(RoleRepository::class)->initTable();
    }

    public function testSearchPermission()
    {
        /** @var PermissionManager $manager */
        $manager = $this->container->get(PermissionManager::class);
        try {
            $permissionId = $manager->getPathId("/");
            $this->assertEquals(PermissionManager::ROOT_ID, $permissionId);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testAddPermission()
    {
        /** @var PermissionManager $manager */
        $manager = $this->container->get(PermissionManager::class);

        $permission = $manager->add("Notepad", "Notepad", PermissionManager::ROOT_ID);

        $this->assertGreaterThan(0, $permission->getId());
        $this->assertSame('notepad', $permission->getCode());
        $this->assertSame('Notepad', $permission->getDescription());

        $path = $manager->getPath($permission->getId());
        $this->assertSame("/notepad", $path);

        $permissionId = $manager->getPathId("/notepad");

        $this->assertSame($permission->getId(), $permissionId);
    }

    /**
     * @depends testAddPermission
     */
    public function testAddDoublePermission()
    {
        /** @var PermissionManager $manager */
        $manager = $this->container->get(PermissionManager::class);

        $permission1 = $manager->add("Notepad", "Notepad", PermissionManager::ROOT_ID);
        $permission2 = $manager->add("Notepad", "Notepad", PermissionManager::ROOT_ID);

        $this->assertSame($permission1->getId(), $permission2->getId());
    }

    /**
     * @depends testAddPermission
     */
    public function testAddSubPermission()
    {
        /** @var PermissionManager $manager */
        $manager = $this->container->get(PermissionManager::class);

        $permission = $manager->add("notepad", "Notepad", PermissionManager::ROOT_ID);
        $subPermission = $manager->add("todolist", "Todo list", $permission->getId());

        $this->assertGreaterThan($permission->getId(), $subPermission->getId(), "Error ID");
        $this->assertGreaterThan($permission->getLeft(), $subPermission->getLeft(), "Error left");
        $this->assertLessThan($permission->getRight(), $subPermission->getRight(), "Error Right {$permission->getRight()} {$subPermission->getRight()}");
    }

    /**
     * @depends testAddSubPermission
     */
    public function testAddDoubleSubPermission()
    {
        /** @var PermissionManager $manager */
        $manager = $this->container->get(PermissionManager::class);

        $permission = $manager->add("notepad", "Notepad", PermissionManager::ROOT_ID);
        $subPermission1 = $manager->add("todolist", "Todo list", $permission->getId());
        $subPermission2 = $manager->add("todolist", "Todo list", $permission->getId());
        $this->assertSame($subPermission1->getId(), $subPermission2->getId());
    }

    public function testAddPath()
    {
        /** @var PermissionManager $manager */
        $manager = $this->container->get(PermissionManager::class);

        $permission = $manager->addPath("/notepad/todolist/read", ['notepad' => 'Notepad', 'todolist' => "Todo list", "read" => "Read Access"]);
        $this->assertGreaterThan(0, $permission->getId());
        $this->assertSame('read', $permission->getCode());
        $this->assertSame('Read Access', $permission->getDescription());

        $subPermission = $manager->getNode($manager->getPathId('/notepad/todolist/read'));
        $permission = $manager->getNode($manager->getPathId('/notepad/todolist'));

        $this->assertGreaterThan($permission->getId(), $subPermission->getId(), "Error ID");
        $this->assertGreaterThan($permission->getLeft(), $subPermission->getLeft(), "Error left");
        $this->assertLessThan($permission->getRight(), $subPermission->getRight(), "Error Right {$permission->getRight()} {$subPermission->getRight()}");
    }

    public function testPath()
    {
        /** @var PermissionManager $manager */
        $manager = $this->container->get(PermissionManager::class);

        $manager->addPath("/notepad/todolist/read", ['notepad' => 'Notepad', 'todolist' => "Todo list", "read" => "Read Access"]);
        $id = $manager->getPathId("/notepad/todolist/read");
        $this->assertSame("/notepad/todolist/read", $manager->getPath($id));
    }

    public function testGetById()
    {
        /** @var PermissionManager $manager */
        $manager = $this->container->get(PermissionManager::class);
        $this->expectException(RbacPermissionNotFoundException::class);
        $id = $manager->getNode(2);
    }

    public function testGetPathId()
    {
        /** @var PermissionManager $manager */
        $manager = $this->container->get(PermissionManager::class);
        $this->expectException(RbacPermissionNotFoundException::class);
        $id = $manager->getPathId("/notepad/todolist/read");
    }

    public function tearDown(): void
    {
        $this->container->get(PermissionRepository::class)->initTable();
        $this->container->get(RoleRepository::class)->initTable();
    }
}
