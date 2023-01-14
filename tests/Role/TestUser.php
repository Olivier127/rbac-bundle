<?php

namespace Test\PhpRbacBundle\Role;

use App\Entity\User;
use PhpRbacBundle\Core\Rbac;
use App\Repository\UserRepository;
use PhpRbacBundle\Core\Manager\RoleManager;
use PhpRbacBundle\Core\Manager\RbacInterface;
use PhpRbacBundle\Core\Manager\PermissionManager;
use PhpRbacBundle\Entity\PermissionInterface;
use PhpRbacBundle\Entity\RoleInterface;

class TestUser
{
    protected static $kernel;

    protected $container;

    protected function setUp(): void
    {
        self::$kernel = self::createKernel();
        self::$kernel->boot();
        $this->container = self::$kernel->getContainer();

        $doctrine = $this->container->get('doctrine');
        $uRepo = $doctrine->getManager()->getRepository(User::class);
        $user = $uRepo->findOneBy(['email' => 'test@test.com']);
        if (is_null($user)) {
            $user = new User();
            $user->setEmail('test@test.com')
                ->setPassword('testpassword')
                ->setRoles(['ROLE_USER']);
            $uRepo->add($user);
        }

        $doctrine->getManager()->getRepository(PermissionInterface::class)->initTable();
        $doctrine->getManager()->getRepository(RoleInterface::class)->initTable();
    }

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

        $user = $uRepo->findOneBy(['email' => 'test@test.com']);
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
        $user = $uRepo->findOneBy(['email' => 'test@test.com']);
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
        $user = $uRepo->findOneBy(['email' => 'test@test.com']);
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
        $user = $uRepo->findOneBy(['email' => 'test@test.com']);
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

    public function tearDown(): void
    {
        $this->container->get(RoleManager::class)->reset();
        $this->container->get(PermissionManager::class)->reset();
        $doctrine = $this->container->get('doctrine');
        /** @var UserRepository $uRepo */
        $uRepo = $doctrine->getManager()->getRepository(User::class);
        $user = $uRepo->findOneBy(['email' => 'test@test.com']);
        $uRepo->remove($user);
    }

    public static function tearDownAfterClass(): void
    {
        $container = self::$kernel->getContainer();
        $doctrine = $container->get('doctrine');

        $container->get(RoleManager::class)->reset();
        $container->get(PermissionManager::class)->reset();

        /** @var UserRepository $uRepo */
        $uRepo = $doctrine->getManager()->getRepository(User::class);
        $user = $uRepo->findOneBy(['email' => 'test@test.com']);
        if (!is_null($user)) {
            $uRepo->remove($user);
        }
    }
}
