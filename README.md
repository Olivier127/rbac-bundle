# PhpRbacBundle

[![Latest Version][ico-version]][link-packagist]
[![Latest Unstable Version][ico-unstable-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]

PhpRBACBundle is symfony 6 bundle with full access control library for PHP. It provides NIST Level 2 Standard Hierarchical Role Based Access Control as an easy to use library to PHP developers. It's a rework of the phprbac.net library made by OWASP for symfony 6.

# How it works ?

Go to https://phprbac.net/ :) to have the representation of permissions and roles as well as their interactions.

<center>
<figure style="background-color: white">
<img src="https://phprbac.net/img/rbac.png"
     alt="Roles and Permissions"
     height="75%" width="75%"
     />
<figcaption>A hierarchical RBAC model of a system Blue: roles, Gray: users, Yellow: permissions
</figcaption>
</figure>
</center>

# Installation

just include the package with composer:

<pre>composer require olivier127/rbac-bundle</pre>

register the bundle inside config/bundles.php

<pre>

return [
    ...
    PhpRbacBundle\PhpRbacBundle::class => ['all' => true],
];
</pre>


Add the PhpRbacBundle\Entity\UserRoleTrait inside the User entity class to add the rbac role relation.

Update the database schema with doctrine migration or doctrine schema update to create all the tables

# Configuration

## Prepare Symfony

Specify the different sections requiring prior authentication in the firewall security configuration section.

Access control only applies to authenticated sections of the website. Therefore, we will use basic ROLE_USER for all users. ROLE_ADMIN can be used for the main administrator but his rights will only be allocated by being associated with the role '/' of the roles tree.

example :
<pre>
# config/packages/security.yaml
security:
    # ...

    role_hierarchy:
        ROLE_ADMIN: ROLE_USER

    access_control:
        - { path: ^/backend, roles: ROLE_USER }
        - { path: ^/todolist, roles: ROLE_USER }
</pre>

## Creation the roles and permissions
Add all the roles and the permissions you need with the RoleManager and the PermissionManager

examples :

to add a permission to the root
<pre>
/** @var PhpRbacBundle\Core\PermissionManager $manager */
$manager = $this->container->get(PermissionManager::class);
$permission = $manager->add("notepad", "Notepad", PermissionManager::ROOT_ID);
</pre>

To add a chain or permission
<pre>
/** @var PhpRbacBundle\Core\PermissionManager $manager */
$manager = $this->container->get(PermissionManager::class);
$manager->addPath("/notepad/todolist/read", ['notepad' => 'Notepad', 'todolist' => "Todo list", "read" => "Read Access"]);
</pre>

## Make the rbac relations

Adding roles use same methods

for the example, i use the chain role "/editor/reviewer". The reviewer is the subrole of the editor, the editor is the subrole of the root "/".
<pre>
/** @var PhpRbacBundle\Core\RoleManager $manager */
$manager = $this->container->get(RoleManager::class);
$manager->addPath("/editor/reviewer", ['editor' => 'Editor', 'reviewer' => "Reviewer"]);
</pre>

Assign permissions to roles
<pre>
/** @var PhpRbacBundle\Core\RoleManager $manager */
$manager = $this->container->get(RoleManager::class);
$editorId = $manager->getPathId("/editor");
$editor = $manager->getNode($editorId);
$reviewerId = $manager->getPathId("/editor/reviewer");
$reviewer = $manager->getNode($reviewerId);

$manager->assignPermission($editor, "/notepad");
$manager->assignPermission($reviewer, "/notepad/todolist/read");
$manager->assignPermission($reviewer, "/notepad/todolist/write");
</pre>


The editor role will have /notepad permission and all sub permissions while the reviewer role will only have /notepad/todolist/read and /notepad/todolist/write permissions

## Assign Role to the user and check permission

If the UserRoleTrait is in the class User, you will have addRbacRole.
Just add the role in this entity

<pre>
/** @var PhpRbacBundle\Core\RoleManager $manager */
$manager = $this->container->get(RoleManager::class);
$editorId = $manager->getPathId("/editor");
$editor = $manager->getNode($editorId);

$user = $userRepository->find($userId);
$user->addRbacRole($user);
$userRepository->add($user, true);
</pre>

To test a user's permission or role, use the PhpRbacBundle\Core\Rbac class.
<pre>
$rbacCtrl = $this->container->get(Rbac::class);
$rbacCtrl->hasPermission('/notepad', $userId);
$rbacCtrl->hasRole('/editor/reviewer', $userId);
</pre>

## RBAC for controller

Just add attribute is granted like this example. The attributes IsGranted and HasRole check the security with the current user.

<pre>
namespace App\Controller;

...
use PhpRbacBundle\Attribute\AccessControl as RBAC;

#[Route('/todolist')]
#[RBAC\IsGranted('/notepad/todolist/read')]
class TodolistController extends AbstractController
{
    #[RBAC\IsGranted('/notepad/todolist/read')]
    #[Route('/', name: 'app_todolist_index', methods: ['GET'])]
    public function index(TodolistRepository $todolistRepository): Response
    {
        ...
    }

    #[RBAC\IsGranted('/notepad/todolist/write')]
    #[Route('/new', name: 'app_todolist_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TodolistRepository $todolistRepository): Response
    {
        ...
    }

    #[RBAC\IsGranted('/notepad/todolist/read')]
    #[Route('/{id}', name: 'app_todolist_show', methods: ['GET'])]
    public function show(Todolist $todolist): Response
    {
        ...
    }

    #[RBAC\IsGranted('/notepad/todolist/write')]
    #[Route('/{id}/edit', name: 'app_todolist_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Todolist $todolist, TodolistRepository $todolistRepository): Response
    {
        ...
    }

    #[RBAC\IsGranted('/notepad/todolist')]
    #[Route('/{id}', name: 'app_todolist_delete', methods: ['POST'])]
    public function delete(Request $request, Todolist $todolist, TodolistRepository $todolistRepository): Response
    {
        ...
    }
}
</pre>

the first RBAC\IsGranted on the class check the lowest permission to access to the controller with the current user.
The RBAC\IsGranted on each action check the minimum permission to make action work.

In the example :
- The permission /notepad/todolist/read gives the access to the all controller and so index and show action.
- The permission /notepad/todolist/write gives the access to edit the todolist
- The permission /notepad/todolist parent to the read and write permission gives the access to delete

The permission /notepad/todolist has also the read and write permission.
