<?php
namespace PhpRbacBundle\Tests\Role;

use Exception;
use App\Entity\User;
use PhpRbacBundle\Core\Rbac;
use App\Repository\UserRepository;
use PhpRbacBundle\Core\RoleManager;
use PhpRbacBundle\Core\RbacInterface;
use PhpRbacBundle\Tests\KernelHelper;
use PhpRbacBundle\Core\PermissionManager;

class TestUser extends KernelHelper
{
    public function testAddRole()
    {
        $doctrine = $this->container->get('doctrine');
        /** @var UserRepository $uRepo */
        $uRepo = $doctrine->getManager()->getRepository(User::class);
        /** @var RoleManager $manager */
        $manager = $this->container->get(RoleManager::class);
        /** @var RbacInterface $rbac */
        $rbac = $this->container->get(Rbac::class);

        $role = $manager->add("editor", "Editor", RoleManager::ROOT_ID);

        $user = $uRepo->find(1);
        $user->addRbacRole($role);
        $uRepo->add($user, true);

        $this->assertTrue($rbac->hasRole($role, $user->getId()));
        $this->assertTrue($rbac->hasRole($role->getId(), $user->getId()));
        $this->assertTrue($rbac->hasRole("/editor", $user->getId()));
        $this->assertFalse($rbac->hasRole("/", $user->getId()));
    }

    public function testAddAdmin()
    {
        $doctrine = $this->container->get('doctrine');
        /** @var UserRepository $uRepo */
        $uRepo = $doctrine->getManager()->getRepository(User::class);
        /** @var RoleManager $rManager */
        $rManager = $this->container->get(RoleManager::class);
        /** @var RbacInterface $rbac */
        $rbac = $this->container->get(Rbac::class);

        $role = $rManager->add("editor", "Editor", RoleManager::ROOT_ID);

        $roleAdmin = $rManager->getNode(RoleManager::ROOT_ID);
        $user = $uRepo->find(1);
        $user->addRbacRole($roleAdmin);
        $uRepo->add($user, true);

        $this->assertTrue($rbac->hasRole($role, $user->getId()));
        $this->assertTrue($rbac->hasRole($role->getId(), $user->getId()));
        $this->assertTrue($rbac->hasRole("/editor", $user->getId()));
        $this->assertTrue($rbac->hasRole("/", $user->getId()));
    }

    public function testEditorPermission()
    {
        $doctrine = $this->container->get('doctrine');
        /** @var UserRepository $uRepo */
        $uRepo = $doctrine->getManager()->getRepository(User::class);

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

        $role = $rManager->getNode($editorId);
        $user = $uRepo->find(1);
        $user->addRbacRole($role);
        $uRepo->add($user, true);

        /** @var RbacInterface $rbac */
        $rbac = $this->container->get(Rbac::class);
        $this->assertTrue($rbac->hasPermission("/notepad", $user->getId()));
        $this->assertTrue($rbac->hasPermission("/notepad/todolist", $user->getId()));
        $this->assertTrue($rbac->hasPermission("/notepad/todolist/read", $user->getId()));
        $this->assertTrue($rbac->hasPermission("/notepad/todolist/write", $user->getId()));
        $this->assertFalse($rbac->hasPermission("/", $user->getId()));
    }

    public function testReviewerPermission()
    {
        $doctrine = $this->container->get('doctrine');
        /** @var UserRepository $uRepo */
        $uRepo = $doctrine->getManager()->getRepository(User::class);

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

        $role = $rManager->getNode($reviewerId);
        $user = $uRepo->find(1);
        $user->addRbacRole($role);
        $uRepo->add($user, true);

        /** @var RbacInterface $rbac */
        $rbac = $this->container->get(Rbac::class);
        $this->assertFalse($rbac->hasPermission("/notepad", $user->getId()));
        $this->assertFalse($rbac->hasPermission("/notepad/todolist", $user->getId()));
        $this->assertTrue($rbac->hasPermission("/notepad/todolist/read", $user->getId()));
        $this->assertTrue($rbac->hasPermission("/notepad/todolist/write", $user->getId()));
        $this->assertFalse($rbac->hasPermission("/", $user->getId()));
    }

    public function tearDown() : void
    {
        $this->container->get(RoleManager::class)->reset();
        $this->container->get(PermissionManager::class)->reset();
        $doctrine = $this->container->get('doctrine');
        /** @var UserRepository $uRepo */
        $uRepo = $doctrine->getManager()->getRepository(User::class);
        $user = $uRepo->find(1);
        foreach ($user->getRbacRoles() as $role) {
            $user->removeRbacRole($role);
        }
        $uRepo->add($user, true);
    }
}
