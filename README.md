# PhpRbacBundle

PhpRBACBundle is symfony 7 bundle with full access control library for PHP. It provides NIST Level 2 Standard Hierarchical Role Based Access Control as an easy to use library to PHP developers. It's a rework of the phprbac.net library made by OWASP for symfony 6.

## ✨ Features

- ✅ **NIST Level 2 RBAC** - Hierarchical role-based access control
- ✅ **PHP 8.3 + Symfony 7.3** - Modern stack with latest features
- ✅ **Multi-Database** - MySQL, MariaDB, PostgreSQL support
- ✅ **Performance Cache** - 80-90% reduction in database queries
- ✅ **Nested Set Model** - Efficient hierarchical permissions
- ✅ **Attributes Support** - PHP 8 attributes for controllers
- ✅ **Voter Integration** - Symfony Security component integration
- ✅ **Twig Extensions** - Template helpers for permissions
- ✅ **CLI Commands** - Interactive management tools

## Table of Content

* [How it works ?](#how-it-works)
* [Installation](#installation)
* [Configuration](#configuration)
    * [Prepare Symfony](#prepare-symfony)
    * [Add PhpRbac configuration](#add-phprbac-configuration)
    * [Cache Configuration](#cache-configuration)
    * [Roles and permissions creation](#roles-and-permissions-creation)
    * [Make the rbac relations](#make-the-rbac-relations)
    * [Assign Role to the user and check permission](#assign-role-to-the-user-and-check-permission)
* [RBAC for controller](#rbac-for-controller)
* [Voter based RBAC](#voter-based-rbac)
* [Cache System](#cache-system)
* [Symfony CLI commands](#symfony-cli-commands)
* [Twig functions](#twig)
* [Documentation](#documentation)

## How it works ?

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

## Installation

just include the package with composer:

<pre>composer require olivier127/rbac-bundle</pre>

register the bundle inside config/bundles.php

```php

return [
    ...
    PhpRbacBundle\PhpRbacBundle::class => ['all' => true],
];
```


Add the PhpRbacBundle\Entity\UserRoleTrait inside the User entity class to add the rbac role relation.

Update the database schema with doctrine migration or doctrine schema update to create all the tables

## Configuration

### Prepare Symfony

Specify the different sections requiring prior authentication in the firewall security configuration section.

Access control only applies to authenticated sections of the website. Therefore, we will use basic ROLE_USER for all users. ROLE_ADMIN can be used for the main administrator but his rights will only be allocated by being associated with the role '/' of the roles tree.

example :
```yaml
# config/packages/security.yaml
security:
    # ...

    role_hierarchy:
        ROLE_ADMIN: ROLE_USER

    access_control:
        - { path: ^/backend, roles: ROLE_USER }
        - { path: ^/todolist, roles: ROLE_USER }
```

### Add PhpRbac configuration

You must create your own entities for driving permissions and roles.

example :

```php
/* src/Entity/Role.php */
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use PhpRbacBundle\Entity\Role as EntityRole;
use PhpRbacBundle\Repository\RoleRepository;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table('rbac_roles')]
class Role extends EntityRole
{

}
```

```php
/* src/Entity/Permission.php */
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use PhpRbacBundle\Entity\Permission as EntityPermission;
use PhpRbacBundle\Repository\PermissionRepository;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table('rbac_permissions')]
class Permission extends EntityPermission
{

}
```

add php_rbac.yaml to associate theses entities to the rbac core
```yaml
# config/packages/php_rbac.yaml
php_rbac:
  no_authentication_section:
    default: deny
  resolve_target_entities:
    user: App\Entity\User
    role: App\Entity\Role
    permission: App\Entity\Permission
  cache:
    enabled: true    # Enable cache for better performance
    ttl: 3600       # Cache TTL in seconds (1 hour)
    prefix: 'rbac_' # Cache key prefix
```

### Cache Configuration

The bundle includes a powerful caching system that reduces database queries by 80-90%. The cache is enabled by default.

**Configuration options:**

- `enabled`: Enable or disable the cache (default: `true`)
- `ttl`: Cache time-to-live in seconds (default: `3600` - 1 hour)
- `prefix`: Cache key prefix to avoid collisions (default: `'rbac_'`)

**Environment-specific configuration:**

```yaml
# config/packages/prod/php_rbac.yaml
php_rbac:
    cache:
        enabled: true
        ttl: 7200  # 2 hours in production

# config/packages/dev/php_rbac.yaml
php_rbac:
    cache:
        enabled: false  # Disable in development for easier debugging
```

For more details, see the [Cache Documentation](docs/CACHE.md).

### Roles and permissions creation
Add all the roles and the permissions you need with the RoleManager and the PermissionManager

examples :

to add a permission to the root
```php
/** @var PhpRbacBundle\Core\PermissionManager $manager */
$manager = $this->container->get(PermissionManager::class);
$permission = $manager->add("notepad", "Notepad", PermissionManager::ROOT_ID);
```

To add a chain or permission
```php
/** @var PhpRbacBundle\Core\PermissionManager $manager */
$manager = $this->container->get(PermissionManager::class);
$manager->addPath("/notepad/todolist/read", ['notepad' => 'Notepad', 'todolist' => "Todo list", "read" => "Read Access"]);
```

## Make the rbac relations

Adding roles use same methods

for the example, i use the chain role "/editor/reviewer". The reviewer is the subrole of the editor, the editor is the subrole of the root "/".
```php
/** @var PhpRbacBundle\Core\RoleManager $manager */
$manager = $this->container->get(RoleManager::class);
$manager->addPath("/editor/reviewer", ['editor' => 'Editor', 'reviewer' => "Reviewer"]);
```

Assign permissions to roles
```php
/** @var PhpRbacBundle\Core\RoleManager $manager */
$manager = $this->container->get(RoleManager::class);
$editorId = $manager->getPathId("/editor");
$editor = $manager->getNode($editorId);
$reviewerId = $manager->getPathId("/editor/reviewer");
$reviewer = $manager->getNode($reviewerId);

$manager->assignPermission($editor, "/notepad");
$manager->assignPermission($reviewer, "/notepad/todolist/read");
$manager->assignPermission($reviewer, "/notepad/todolist/write");
```


The editor role will have /notepad permission and all sub permissions while the reviewer role will only have `/notepad/todolist/read` and `/notepad/todolist/write` permissions

### Assign Role to the user and check permission

If the `UserRoleTrait` is in the class `User`, you will have `addRbacRole`.
Just add the role in this entity

```php
/** @var PhpRbacBundle\Core\RoleManager $manager */
$manager = $this->container->get(RoleManager::class);
$editorId = $manager->getPathId("/editor");
$editor = $manager->getNode($editorId);

$user = $userRepository->find($userId);
$user->addRbacRole($user);
$userRepository->add($user, true);
```

To test a user's permission or role, use the PhpRbacBundle\Core\Rbac class.
```php
$rbacCtrl = $this->container->get(Rbac::class);
$rbacCtrl->hasPermission('/notepad', $userId);
$rbacCtrl->hasRole('/editor/reviewer', $userId);
```

## RBAC for controller

Just add attribute is granted like this example. The attributes `IsGranted` and `HasRole` check the security with the current user.

```php
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
```

the first RBAC\IsGranted on the class check the lowest permission to access to the controller with the current user.
The `RBAC\IsGranted` on each action check the minimum permission to make action work.

In the example :
- The permission `/notepad/todolist/read` gives the access to the all controller and so index and show action.
- The permission `/notepad/todolist/write` gives the access to edit the todolist
- The permission `/notepad/todolist` parent to the read and write permission gives the access to delete

The permission `/notepad/todolist` has also the read and write permission.

## Voter based Rbac

With RbacVoter, you can use symfony security to check the user rbac permissions (not the roles).

example:

```php
    #[IsGranted('/todolist/index', statusCode: 403, message: 'Access denied for user')]
    #[Route('/', name: 'app_todo_list_index', methods: ['GET'])]
    public function index(TodoListRepository $todoListRepository): Response
```

You need to set the security access control to be unanimous (all the voter must be ok)

add this lines to `config/packages/security.yaml`</pre>

```yaml
security:
    ...
    access_decision_manager:
        strategy: unanimous
        allow_if_all_abstain: false
```

## Cache System

The bundle includes a high-performance caching system that dramatically reduces database queries.

### Clear Cache Commands

```bash
# Clear all RBAC cache
php bin/console security:rbac:cache:clear

# Clear only permissions cache
php bin/console security:rbac:cache:clear --permissions

# Clear only roles cache
php bin/console security:rbac:cache:clear --roles

# Clear cache for specific user
php bin/console security:rbac:cache:clear --user=42
```

### Performance Impact

- **80-90% reduction** in database queries
- First check: 15ms (DB + cache)
- Subsequent checks: 0.5ms (cache only)
- Over 100 checks: 1500ms → 65ms (95.7% faster)

For complete cache documentation, see [docs/CACHE.md](docs/CACHE.md).

## Symfony CLI commands

  The install command sets the root node role and permission and associates them.
  ```shell
    security:rbac:install
  ```

  Add permission into the rbac permissions tree
  ```shell
  security:rbac:permission:add
  ```

  Add permission into the rbac roles tree
  ```shell
  security:rbac:role:add
  ```

  Assign a permission to a role
  ```shell
  security:rbac:role:assign-permission
  ```

  Assign a role to a user
  ```shell
  security:rbac:user:assign-role
  ```

  Clear RBAC cache
  ```shell
  security:rbac:cache:clear
  ```

  Theses commandes are interactives.

  ## Twig

  test if user has a role
  ```twig
  {% if hasRole('/the/role') %}
  ...
  {% endif %}
  ```

  test if user has a permission
  ```twig
  {% if hasPermission('/the/permission') %}
  ...
  {% endif %}
  ```

## Documentation

- **[Cache System](docs/CACHE.md)** - Complete cache documentation

## Requirements

- PHP 8.3 or higher
- Symfony 7.3 or higher
- Doctrine ORM 3.3 or higher
- MySQL 5.7+, MariaDB 10.2+, or PostgreSQL 12+

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.
