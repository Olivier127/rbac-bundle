<?php
namespace Test\PhpRbacBundle\Role;

use Exception;
use Test\PhpRbacBundle\KernelHelper;
use PhpRbacBundle\Core\Manager\RoleManager;
use PhpRbacBundle\Core\Manager\PermissionManager;
use PhpRbacBundle\Exception\RbacRoleNotFoundException;

class TestRole extends KernelHelper
{

    public function testSearchRole()
    {
        /** @var RoleManager $manager */
        $manager = $this->container->get(RoleManager::class);
        try {
            $roleId = $manager->getPathId("/");
            $this->assertEquals(RoleManager::ROOT_ID, $roleId);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testAddRole()
    {
        /** @var RoleManager $manager */
        $manager = $this->container->get(RoleManager::class);

        $role = $manager->add("Editor", "Editor", RoleManager::ROOT_ID);
        $this->assertGreaterThan(0, $role->getId());
        $this->assertSame('editor', $role->getTitle());
        $this->assertSame('Editor', $role->getDescription());

        $path = $manager->getPath($role->getId());
        $this->assertSame("/editor", $path);

        $roleId = $manager->getPathId("/editor");

        $this->assertSame($role->getId(), $roleId);
    }

    /**
     * @depends testAddRole
     */
    public function testAddDoubleRole()
    {
        /** @var RoleManager $manager */
        $manager = $this->container->get(RoleManager::class);

        $role1 = $manager->add("Editor", "Editor", RoleManager::ROOT_ID);
        $role2 = $manager->add("Editor", "Editor", RoleManager::ROOT_ID);

        $this->assertSame($role1->getId(), $role2->getId());
    }

    /**
     * @depends testAddRole
     */
    public function testAddSubRole()
    {
        /** @var RoleManager $manager */
        $manager = $this->container->get(RoleManager::class);

        $role = $manager->add("Editor", "Editor", RoleManager::ROOT_ID);
        $subRole = $manager->add("reviewer", "Reviewer", $role->getId());

        $this->assertGreaterThan($role->getId(), $subRole->getId(), "Error ID");
        $this->assertGreaterThan($role->getLeft(), $subRole->getLeft(), "Error left");
        $this->assertLessThan($role->getRight(), $subRole->getRight(), "Error Right {$role->getRight()} {$subRole->getRight()}");
    }

    /**
     * @depends testAddSubRole
     */
    public function testAddDoubleSubRole()
    {
        /** @var RoleManager $manager */
        $manager = $this->container->get(RoleManager::class);

        $role = $manager->add("Editor", "Editor", RoleManager::ROOT_ID);
        $subRole1 = $manager->add("reviewer", "Reviewer", $role->getId());
        $subRole2 = $manager->add("reviewer", "Reviewer", $role->getId());
        $this->assertSame($subRole1->getId(), $subRole2->getId());
    }

    /**
     * @depends testAddRole
     */
    public function testAddPath()
    {
        /** @var RoleManager $manager */
        $manager = $this->container->get(RoleManager::class);

        $role = $manager->addPath("/editor/reviewer", ['editor' => 'Editor', 'reviewer' => "Reviewer"]);
        $this->assertGreaterThan(0, $role->getId());
        $this->assertSame('reviewer', $role->getTitle());
        $this->assertSame('Reviewer', $role->getDescription());

        $role = $manager->getNode($manager->getPathId('/editor'));
        $subRole = $manager->getNode($manager->getPathId('/editor/reviewer'));

        $this->assertGreaterThan($role->getId(), $subRole->getId(), "Error ID");
        $this->assertGreaterThan($role->getLeft(), $subRole->getLeft(), "Error left");
        $this->assertLessThan($role->getRight(), $subRole->getRight(), "Error Right {$role->getRight()} {$subRole->getRight()}");
    }

    public function testPath()
    {
        /** @var RoleManager $manager */
        $manager = $this->container->get(RoleManager::class);

        $manager->addPath("/editor/reviewer", ['editor' => 'Editor', 'reviewer' => "Reviewer"]);
        $id = $manager->getPathId("/editor/reviewer");
        $this->assertSame("/editor/reviewer", $manager->getPath($id));
    }

    public function testGetById()
    {
        /** @var RoleManager $manager */
        $manager = $this->container->get(RoleManager::class);
        $this->expectException(RbacRoleNotFoundException::class);
        $id = $manager->getNode(2);
        $this->expectException(RbacRoleNotFoundException::class);
        $id = $manager->getPathId("/editor/reviewer");
    }

    public function testGetPathId()
    {
        /** @var RoleManager $manager */
        $manager = $this->container->get(RoleManager::class);
        $this->expectException(RbacRoleNotFoundException::class);
        $id = $manager->getPathId("/editor/reviewer");
    }

    public function testAssign()
    {
        /** @var PermissionManager $pManager */
        $pManager = $this->container->get(PermissionManager::class);

        $pManager->addPath("/notepad/todolist/read", ['notepad' => 'Notepad', 'todolist' => "Todo list", "read" => "Read Access"]);
        $pManager->addPath("/notepad/todolist/write", ['notepad' => 'Notepad', 'todolist' => "Todo list", "write" => "Write Access"]);

        /** @var RoleManager $rManager */
        $rManager = $this->container->get(RoleManager::class);
        $rManager->addPath("/editor/reviewer", ['editor' => 'Editor', 'reviewer' => "Reviewer"]);

        $editorId = $rManager->getPathId("/editor");
        $editor = $rManager->getNode($editorId);
        $reviewerId = $rManager->getPathId("/editor/reviewer");
        $reviewer = $rManager->getNode($reviewerId);

        $rManager->assignPermission($editor, "/notepad");
        $rManager->assignPermission($reviewer, "/notepad/todolist/read");
        $rManager->assignPermission($reviewer, "/notepad/todolist/write");

        $perm1 = $pManager->getPathId('/notepad');
        $perm2 = $pManager->getPathId('/notepad/todolist');
        $perm3 = $pManager->getPathId('/notepad/todolist/read');
        $perm4 = $pManager->getPathId('/notepad/todolist/write');

        $this->assertTrue($rManager->hasPermission($editor->getId(), $perm1));
        $this->assertTrue($rManager->hasPermission($editor->getId(), $perm2));
        $this->assertTrue($rManager->hasPermission($editor->getId(), $perm3));
        $this->assertTrue($rManager->hasPermission($editor->getId(), $perm4));
        $this->assertFalse($rManager->hasPermission($editor->getId(), PermissionManager::ROOT_ID));

        $this->assertTrue($rManager->hasPermission($reviewer->getId(), $perm3));
        $this->assertTrue($rManager->hasPermission($reviewer->getId(), $perm4));
        $this->assertFalse($rManager->hasPermission($reviewer->getId(), $perm2));
    }

    public function testUnassign()
    {
        /** @var PermissionManager $pManager */
        $pManager = $this->container->get(PermissionManager::class);

        $pManager->addPath("/notepad/todolist/read", ['notepad' => 'Notepad', 'todolist' => "Todo list", "read" => "Read Access"]);
        $pManager->addPath("/notepad/todolist/write", ['notepad' => 'Notepad', 'todolist' => "Todo list", "write" => "Write Access"]);

        /** @var RoleManager $rManager */
        $rManager = $this->container->get(RoleManager::class);
        $rManager->addPath("/editor/reviewer", ['editor' => 'Editor', 'reviewer' => "Reviewer"]);

        $editorId = $rManager->getPathId("/editor");
        $editor = $rManager->getNode($editorId);
        $reviewerId = $rManager->getPathId("/editor/reviewer");
        $reviewer = $rManager->getNode($reviewerId);

        $rManager->assignPermission($editor, "/notepad");
        $rManager->assignPermission($reviewer, "/notepad/todolist/read");
        $rManager->assignPermission($reviewer, "/notepad/todolist/write");

        $perm4 = $pManager->getPathId('/notepad/todolist/write');
        $this->assertTrue($rManager->hasPermission($reviewer->getId(), $perm4));
        $rManager->unassignPermission($reviewer, "/notepad/todolist/write");
        $this->assertFalse($rManager->hasPermission($reviewer->getId(), $perm4));
        $rManager->assignPermission($reviewer, "/notepad/todolist/write");
        $this->assertTrue($rManager->hasPermission($reviewer->getId(), $perm4));
    }

    public function tearDown() : void
    {
        $this->container->get(RoleManager::class)->reset();
        $this->container->get(PermissionManager::class)->reset();
    }
}
