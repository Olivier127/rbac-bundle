<?php
declare(strict_types=1);

namespace PhpRbacBundle\Attribute\AccessControl;

use Attribute;
use PhpRbacBundle\Core\RbacInterface;
use PhpRbacBundle\Attribute\RBACAttributeInterface;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class IsGranted implements RBACAttributeInterface
{
    public function __construct(
        private string $permission,
        private ?int $statusCode=403,
        private ?string $message = 'This ressource is not allowed for the current user'
    ) {
    }

    public function getSecurityCheckMethod(RbacInterface $accessControl, mixed $userId): bool
    {
        return $accessControl->hasPermission($this->permission, $userId);
    }
}