<?php

namespace Test\PhpRbacBundle;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class KernelHelper extends KernelTestCase
{
    static protected $kernel;

    /**
     * Container
     *
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp(): void
    {
        self::$kernel = self::createKernel();
        self::$kernel->boot();
        $this->container = self::$kernel->getContainer();
    }
}
