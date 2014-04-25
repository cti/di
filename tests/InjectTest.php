<?php

use Cti\Di\Manager;

class InjectTest extends \PHPUnit_Framework_TestCase
{
    private $manager;

    private function getManager()
    {
        if (!$this->manager) {
            $this->manager = new Manager();
        }
        return $this->manager;
    }

    public function testFullClassName()
    {
        $container = $this->getManager()->get('Inject\FullClassName');
        $this->assertNotEmpty($container->fakeClass);
        $this->assertTrue($container->fakeClass->initialized);
        $this->assertEmpty($container->namespace);
    }

    public function testShortClassName()
    {
        $container = $this->getManager()->get('Inject\ShortClassName');
        $this->assertNotEmpty($container->fakeClass);
        $this->assertTrue($container->fakeClass->initialized);
    }

    public function testByNamespace()
    {
        $container = $this->getManager()->get('Inject\ByNamespace');
        $this->assertNotEmpty($container->fakeClass);
        $this->assertTrue($container->fakeClass->initialized);
    }


} 