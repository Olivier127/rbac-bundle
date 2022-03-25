<?php

namespace PhpRbacBundle\EventSubscriber;

use ReflectionMethod;
use Psr\Log\LoggerInterface;
use PhpRbacBundle\Core\RbacInterface;
use App\Attribute\AccessControl\HasRole;
use App\Attribute\AccessControl\IsGranted;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccessControlDriver implements EventSubscriberInterface
{
    public function __construct(
        private RbacInterface $accessControl,
        private LoggerInterface $accessControlLogger
    ) {
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
            $token = $event->getRequest()->query->get('token');
            if (empty($token)) {
                $this->accessControlLogger->debug('IsGranted on anonymous action', compact('controller', 'method'));
                return;
            }
            $attribute = $attributes[0]->newInstance();
            $allowed = $attribute->getSecurityCheckMethod($this->accessControl, "Identifiant utilisateur");
            if (!$allowed) {
                $this->accessControlLogger->critical('Action forbidden for user', compact('controller', 'method'));
                throw new HttpException($attribute->statusCode, $attribute->message);
            }
            $this->accessControlLogger->info('Action allowed for user', compact('controller', 'method'));
        }
    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}