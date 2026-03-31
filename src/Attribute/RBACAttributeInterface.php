<?php

declare(strict_types=1);

namespace PhpRbacBundle\Attribute;

use PhpRbacBundle\Core\RbacInterface;

interface RBACAttributeInterface
{
    public function getSecurityCheckMethod(RbacInterface $accessControl, mixed $userId): bool;
}
