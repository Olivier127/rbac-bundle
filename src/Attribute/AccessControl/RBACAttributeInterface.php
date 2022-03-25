<?php
declare(strict_types=1);

namespace PhpRbacBundle\Attribute\AccessControl;

use Attribute;
use PhpRbacBundle\Core\RbacInterface;

#[Attribute]
interface RBACAttributeInterface
{
    public function getSecurityCheckMethod(RbacInterface $accessControl, mixed $userId) : bool;
}