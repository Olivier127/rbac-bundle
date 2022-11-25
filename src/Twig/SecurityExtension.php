<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpRbacBundle\Twig;

use Twig\TwigFunction;
use PhpRbacBundle\Core\RbacInterface;
use PhpRbacBundle\Exception\RbacException;
use Twig\Extension\AbstractExtension;
use Symfony\Component\Security\Core\Security;

/**
 * SecurityExtension exposes security context features.
 *
 * @author Olivier Fouache <olivier.fouache@gmail.com>
 */
final class SecurityExtension extends AbstractExtension
{
    public function __construct(
        private RbacInterface $rbacInterface,
        private Security $security
    ) {
    }

    public function hasPermission(string $permission): bool
    {
        if (empty($this->security->getUser())) {
            return false;
        }

        try {
            return $this->rbacInterface->hasPermission($permission, $this->security->getUser()->getId());
        } catch (RbacException) {
            return false;
        }
    }

    public function hasRole(string $role): bool
    {
        if (empty($this->security->getUser())) {
            return false;
        }

        try {
            return $this->rbacInterface->hasRole($role, $this->security->getUser()->getId());
        } catch (RbacException) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('has_permission', [$this, 'hasPermission']),
            new TwigFunction('has_role', [$this, 'hasRole']),
        ];
    }
}
