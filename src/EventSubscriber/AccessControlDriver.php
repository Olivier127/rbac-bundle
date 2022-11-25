<?php

namespace PhpRbacBundle\EventSubscriber;

use ReflectionMethod;
use Psr\Log\LoggerInterface;
use PhpRbacBundle\Core\RbacInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use PhpRbacBundle\Attribute\AccessControl\HasRole;
use PhpRbacBundle\Attribute\AccessControl\IsGranted;
use PhpRbacBundle\Exception\RbacException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Security;

class AccessControlDriver implements EventSubscriberInterface
{
    public function __construct(
        private RbacInterface $accessControl,
        private LoggerInterface $accessControlLogger,
        private Security $security
    ) {
    }

    private array $config = [
        'no_authentication_section' => [
            'default' => 'deny'
        ]
    ];

    public function load(array $config)
    {
        $this->config = $config;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controllers = $event->getController();
        if (!is_array($controllers)) {
            return;
        }
        $controller = $controllers[0];
        $method = $controllers[1];

        $reflection = new ReflectionMethod($controller, $method);
        $attributes = $reflection->getAttributes(IsGranted::class) + $reflection->getAttributes(HasRole::class);
        if (is_array($attributes) && !empty($attributes)) {
            $user = $this->security->getUser();
            if (empty($user)) {
                if (strtolower($this->config['no_authentication_section']['default']) == 'allow') {
                    $this->accessControlLogger->debug('IsGranted on anonymous action', compact('controller', 'method'));
                    return;
                }
                throw new RbacException("Anonymous user on protected controller/action", 403);
            }
            $attribute = $attributes[0]->newInstance();
            $allowed = $attribute->getSecurityCheckMethod($this->accessControl, $user->getId());
            if (!$allowed) {
                $this->accessControlLogger->critical('Action forbidden for user', compact('controller', 'method'));
                throw new HttpException($attribute->statusCode, $attribute->message);
            }
            $this->accessControlLogger->info('Action allowed for user', compact('controller', 'method'));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
