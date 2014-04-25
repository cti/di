<?php

use Cti\Di\Manager;
use Cti\Di\Parser;
use Cti\Di\Reflection;

class UsageTests extends PHPUnit_Framework_TestCase
{
    function testUsing()
    {
        $parser = new Parser();

        $usage = $parser->getUsage(Reflection::getReflectionClass('Common\Usage'));

        $this->assertArrayHasKey('Manager', $usage);
        $this->assertArrayHasKey('Config', $usage);

        $this->assertSame($usage, array(
            'Manager' => 'Cti\Di\Manager',
            'Config' => 'Cti\Di\Configuration',
        ));

        $manager = new Manager();
        /**
         * @var \Common\Usage $usage
         */
        $usage = $manager->create('Common\Usage');
        $this->assertSame($usage->manager, $manager);
        $this->assertSame($usage->config, $manager->getConfiguration());
        $this->assertSame($usage->globalManager, $manager);
    }
}
