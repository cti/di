<?php

use Common\Application;
use Common\Module;
use Cti\Di\Configuration;
use Cti\Di\Manager;
use Cti\Di\Reference;

class NewTests extends PHPUnit_Framework_TestCase
{
    function testNew()
    {
        $manager = new Manager;
        $new = $manager->getInstance('Common\InjectNew');
        $this->assertInstanceOf('Common\InjectNew', $new);

        $this->assertInstanceOf('Common\Module', $new->module1);
        $this->assertInstanceOf('Common\Module', $new->module2);

        $this->assertNotSame($new->module1, $new->module2);

        $this->assertSame($manager->getInstances('Common\Module'), array($new->module1, $new->module2));

        $new2 = $manager->create('Common\InjectNew');
        $this->assertInstanceOf('Common\InjectNew', $new2);

        $this->assertInstanceOf('Common\Module', $new2->module1);
        $this->assertInstanceOf('Common\Module', $new2->module2);
        $this->assertNotSame($new2->module1, $new2->module2);

        $this->assertNotSame($new->module1, $new2->module1);
        $this->assertNotSame($new->module2, $new2->module2);
        $this->assertSame($manager->getInstances('Common\Module'), array($new->module1, $new->module2, $new2->module1, $new2->module2));


        $references = array($new, $new, $new2, $new2);
        foreach($manager->getInstances('Common\Module') as $module) {
            foreach($manager->getInjector()->getReferences($module) as $reference) {
                $actual[] = $reference['instance'];
            }
        }

        $this->assertSame($references, $actual);
    }
}