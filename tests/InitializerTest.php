<?php

use Common\Module;
use Cti\Di\Manager;

class InitializerTest extends \PHPUnit_Framework_TestCase
{
    public function testBefore()
    {
        $manager = new Manager();
        $manager->getInitializer()->before('Common\\Module', function($module) {
            $module->state = 'zzz';
        });

        $module = $manager->get('Common\\Module');
        $this->assertSame($module->new_state, 'zzz!');
    }

    public function testAfter()
    {
        $manager = new Manager();
        $manager->getInitializer()->after('Common\\Module', function($module) {
            $module->state = 'zzz';
        });

        $module = $manager->create('Common\\Module', array(
            'state' => 'a'
        ));

        $this->assertSame($module->new_state, 'a!');
        $this->assertSame($module->state, 'zzz');
    }

    public function testMethod()
    {
        $manager = new Manager();
        $manager->getInitializer()->before('Common\Module', array($this, 'callBefore'));
        $manager->getInitializer()->after('Common\Module', array(__CLASS__, 'callAfter'));

        $module = $manager->create('Common\\Module');

        $this->assertSame($module->state, 'Cti\\Di\\Manager');
        $this->assertSame($module->reference, $manager);
    }

    public function callBefore(Module $module, Manager $manager)
    {
        $module->state = get_class($manager);
    }

    public function callAfter(Module $module, Manager $manager)
    {
        $module->reference = $manager;
    }
} 