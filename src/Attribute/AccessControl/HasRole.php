<?php

declare(strict_types=1);

namespace PhpRbacBundle\Attribute\AccessControl;

use Attribute;
use PhpRbacBundle\Core\RbacInterface;
use PhpRbacBundle\Attribute\RBACAttributeInterface;
use PhpRbacBundle\Exception\RbacException;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class HasRole implements RBACAttributeInterface
{
    public function __construct(
        public readonly string $role = "",
        public readonly ?int $statusCode = 403,
        public readonly ?string $message = 'This resource is not allowed for the current user'
    ) {
    }

    public function getSecurityCheckMethod(RbacInterface $accessControl, mixed $userId): bool
    {
        try {
            return $accessControl->hasRole($this->role, $userId);
        } catch (RbacException) {
            return false;
        }
    }
}
