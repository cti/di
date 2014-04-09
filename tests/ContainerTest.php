<?php

use Cti\Di\Container;

class ContainerTest extends PHPUnit_Framework_TestCase
{

    function testContainer()
    {
        // array configuration
        $container = new Container(array(
            'config' => array('a' => array('b')),
            'services' => array('q' => 'Common\Module'),
        ));

        $this->assertSame($container->getConfiguration()->get('a'), array('b'));
        $this->assertInstanceOf('Common\Module', $container->getLocator()->get('q'));
        $this->assertInstanceOf('Cti\Di\Manager', $container->getManager());

        $container = new Container(array(
            'config' => __DIR__ . '/resources/config.php',
            'services' => __DIR__ . '/resources/services.php',
        ));

        // config property was overriden by local config
        $this->assertSame($container->getConfiguration()->get('a'), array('a2'));
    }

    function testFailConfig()
    {
        $this->setExpectedException('Exception');
        $container = new Container(array(
            'config' => __DIR__ . '/resources/no-config.php',
            'services' => __DIR__ . '/resources/services.php',
        ));
        
    }

    function testFailServices()
    {
        $this->setExpectedException('Exception');        
        $container = new Container(array(
            'config' => __DIR__ . '/resources/config.php',
            'services' => __DIR__ . '/resources/no-services.php',
        ));

    }
}