<?php

use Cti\Di\Manager;
use Cti\Di\Parser;
use Cti\Di\Reflection;

class UsageTests extends PHPUnit_Framework_TestCase
{
    function testUsing()
    {
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
