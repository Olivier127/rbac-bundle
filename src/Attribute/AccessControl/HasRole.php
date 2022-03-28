<?php
declare(strict_types=1);

namespace PhpRbacBundle\Attribute\AccessControl;

use Attribute;
use PhpRbacBundle\Core\RbacInterface;
use PhpRbacBundle\Attribute\RBACAttributeInterface;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class HasRole implements RBACAttributeInterface
{
    public function __construct(
        private string $role,
        private ?int $statusCode=403,
        private ?string $message = 'This ressource is not allowed for the current user'
    ) {
    }

    public function getSecurityCheckMethod(RbacInterface $accessControl, mixed $userId): bool
    {
        return $accessControl->hasRole($this->role, $userId);
    }

}