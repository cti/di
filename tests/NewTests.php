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
		$new = $manager->get('Common\InjectNew');
		$this->assertInstanceOf('Common\InjectNew', $new);

		$this->assertInstanceOf('Common\Module', $new->module1);
		$this->assertInstanceOf('Common\Module', $new->module2);

		$this->assertNotSame($new->module1, $new->module2);

		$this->assertSame($manager->getInstances('Common\Module'), array($new->module1, $new->module2));
	}
}