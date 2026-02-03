<?php

namespace PhpRbacBundle\Core;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class RbacCacheService
{
    private bool $enabled;
    private int $ttl;
    private string $prefix;

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        bool $enabled = true,
        int $ttl = 3600,
        string $prefix = 'rbac_'
    ) {
        $this->enabled = $enabled;
        $this->ttl = $ttl;
        $this->prefix = $prefix;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get cached permission check result
     *
     * @param string|int $permission
     * @param mixed $userId
     * @return bool|null Returns null if not in cache
     */
    public function getPermission(string|int $permission, mixed $userId): ?bool
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $key = $this->buildPermissionKey($permission, $userId);
            $item = $this->cache->getItem($key);

            if ($item->isHit()) {
                return $item->get();
            }
        } catch (InvalidArgumentException) {
            return null;
        }

        return null;
    }

    /**
     * Cache permission check result
     *
     * @param string|int $permission
     * @param mixed $userId
     * @param bool $result
     * @return void
     */
    public function setPermission(string|int $permission, mixed $userId, bool $result): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $key = $this->buildPermissionKey($permission, $userId);
            $item = $this->cache->getItem($key);
            $item->set($result);
            $item->expiresAfter($this->ttl);
            $this->cache->save($item);
        } catch (InvalidArgumentException) {
            // Silently fail if cache key is invalid
        }
    }

    /**
     * Get cached role check result
     *
     * @param string|int $role
     * @param mixed $userId
     * @return bool|null Returns null if not in cache
     */
    public function getRole(string|int $role, mixed $userId): ?bool
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $key = $this->buildRoleKey($role, $userId);
            $item = $this->cache->getItem($key);

            if ($item->isHit()) {
                return $item->get();
            }
        } catch (InvalidArgumentException) {
            return null;
        }

        return null;
    }

    /**
     * Cache role check result
     *
     * @param string|int $role
     * @param mixed $userId
     * @param bool $result
     * @return void
     */
    public function setRole(string|int $role, mixed $userId, bool $result): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $key = $this->buildRoleKey($role, $userId);
            $item = $this->cache->getItem($key);
            $item->set($result);
            $item->expiresAfter($this->ttl);
            $this->cache->save($item);
        } catch (InvalidArgumentException) {
            // Silently fail if cache key is invalid
        }
    }

    /**
     * Clear all RBAC cache
     *
     * @return bool
     */
    public function clearAll(): bool
    {
        return $this->cache->clear();
    }

    /**
     * Clear permission cache for a specific user or all users
     *
     * @param mixed|null $userId If null, clears all permission cache
     * @return void
     */
    public function clearPermissions(?int $userId = null): void
    {
        if ($userId !== null) {
            $this->clearByPattern($this->prefix . 'perm_' . $userId . '_*');
        } else {
            $this->clearByPattern($this->prefix . 'perm_*');
        }
    }

    /**
     * Clear role cache for a specific user or all users
     *
     * @param mixed|null $userId If null, clears all role cache
     * @return void
     */
    public function clearRoles(?int $userId = null): void
    {
        if ($userId !== null) {
            $this->clearByPattern($this->prefix . 'role_' . $userId . '_*');
        } else {
            $this->clearByPattern($this->prefix . 'role_*');
        }
    }

    /**
     * Clear cache for a specific user
     *
     * @param mixed $userId
     * @return void
     */
    public function clearUser(mixed $userId): void
    {
        $this->clearPermissions($userId);
        $this->clearRoles($userId);
    }

    /**
     * Build cache key for permission
     *
     * @param string|int $permission
     * @param mixed $userId
     * @return string
     */
    private function buildPermissionKey(string|int $permission, mixed $userId): string
    {
        $permissionKey = is_string($permission) ? md5($permission) : $permission;
        return $this->prefix . 'perm_' . $userId . '_' . $permissionKey;
    }

    /**
     * Build cache key for role
     *
     * @param string|int $role
     * @param mixed $userId
     * @return string
     */
    private function buildRoleKey(string|int $role, mixed $userId): string
    {
        $roleKey = is_string($role) ? md5($role) : $role;
        return $this->prefix . 'role_' . $userId . '_' . $roleKey;
    }

    /**
     * Clear cache by pattern (implementation depends on cache adapter)
     *
     * @param string $pattern
     * @return void
     */
    private function clearByPattern(string $pattern): void
    {
        // Note: Pattern-based clearing is not supported by PSR-6
        // For production, consider using Symfony's TagAwareCacheInterface
        // For now, we clear all cache when pattern is used
        $this->cache->clear();
    }
}
