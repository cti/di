<?php

use Common\Application;
use Common\Module;
use Cti\Di\Configuration;
use Cti\Di\Manager;
use Cti\Di\Reference;

class DiTests extends PHPUnit_Framework_TestCase
{
    public function testCustomManager()
    {
        $manager = new \Common\CustomManager();
        $this->assertSame($manager, $manager->get('Cti\\Di\\Manager'));
    }

    public function testProtectedInit()
    {
        $manager = new Manager;
        $instance = $manager->get('Common\ProtectedInit');
        $this->assertTrue($instance->inited);
    }
    public function testPropertiesConfiguration()
    {
        $manager = new Manager;
        $manager->setConfigureAllProperties(true);

        $module = $manager->create('Common\Module', array(
            '_state' => '_state'
        ));
        $this->assertSame($module->getProtectedState(), '_state');

        $manager->setConfigureAllProperties(true);
        $this->assertTrue($manager->getConfigureAllProperties());

        $manager->setConfigureAllProperties(false);
        $this->assertFalse($manager->getConfigureAllProperties());        

        $module = $manager->create('Common\Module', array(
            '_state' => '_state'
        ));
        $this->assertSame($module->getProtectedState(), null);
    }

    public function testInstanceInjection()
    {
        $manager = new Manager();
        $module = new Module();
        $app = new Application();

        $result = $manager->call($this, 'callInjection', array($module, $app));
        $this->assertSame($result, array($app, $module));
    }

    public function callInjection(Application $app, Module $module)
    {
        return array($app, $module);
    }

    public function testFilesystemLoader()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__ . '/resources/config.php');

        // local config
        $this->assertSame($configuration->get('a'), array('a2'));

        $configuration->set('a', 0, 'a3');
        $this->assertSame($configuration->get('a'), array('a3'));

        $configuration->load(array('a' => array('a4')));
        $this->assertSame($configuration->get('a'), array('a4'));

    }

    public function testFailParsing()
    {
        $this->setExpectedException('Exception');
        $locator = new Configuration;
        $locator->load('?');
    }
    

    public function testInstanceConfiguration()
    {
        $configuration = new Configuration(array(
            'Common\Module' => array(
                'state' => 'active'
            )
        ));

        $m = new Manager($configuration);

        $this->assertSame($configuration, $m->getConfiguration());

        $this->assertSame($m->get('Common\Module')->getState(), 'active');
        $this->assertSame($m->create('Common\Module')->getState(), 'active');

        // change configuration
        $configuration->set('Common\Module', 'state', 'disabled');
        $this->assertSame($m->create('Common\Module')->getState(), 'disabled');

        // override configuration
        $instance = $m->create('Common\Module', array(
            'state' => 'unknown'
        ));
        $this->assertSame($instance->getState(), 'unknown');

        // define new class configuration
        $configuration->set('Common\ClassWithConstructor', 'property', 'tra-ta-ta');
        $this->assertSame($m->create('Common\ClassWithConstructor')->property, 'tra-ta-ta');

        // get value from configuration
        $value = $configuration->get('Common\ClassWithConstructor', 'emptyProperty', 'defaultValue');
        $this->assertSame($value, 'defaultValue');

        // merge configuration
        $configuration->merge(array(
            'Common\Application' => array(
                'k' => 'v'
            ),
            'Common\ClassWithConstructor' => array(
                'emptyProperty' => 'concreteValue',
                'property' => 'piu-piu'
            )
        ));
        $this->assertSame($configuration->get('Common\ClassWithConstructor', 'property'), 'piu-piu');

        // push tests
        $configuration->push('class', 'property', 'item');
        $configuration->push('class', 'property', 'item2');

        // test scalar push
        $this->setExpectedException('Exception');
        $configuration->set('class', 'scalar', 'string');
        $configuration->push('class', 'scalar', 'string');
    }

    public function testPushKey()
    {
        $configuration = new Configuration;
        $configuration->push('class', 'property', 'value', 'index');

        $this->setExpectedException('Exception');
        $configuration->push('class', 'property', 'value2', 'index');
    }

    public function testPropertyInjection() 
    {
        $m = new Manager;
        $module = $m->get('Common\Application')->getModule();
        $this->assertSame($module, $m->get('Common\Module'));
    }

    public function testAlias()
    {
        $manager = new Manager;
        $manager->getConfiguration()->setAlias('app', 'Common\Application');
        $this->assertSame($manager->get('app'), $manager->get('Common\Application'));
        $this->assertInstanceOf('Common\Application', $manager->create('app'));
    }

    public function testAliasInConfiguration()
    {
        $configuration = new Configuration(array(
            'app' => 'Common\Application'
        ));

        $manager = new Manager($configuration);
        $this->assertInstanceOf('Common\Application', $manager->get('app'));
    }

    public function testInterfaceImplementation()
    {
        $manager = new Manager();
        $manager->getConfiguration()->setAlias('Common\GatewayInterface', 'Common\ProxyGateway');

        $this->assertInstanceOf('Common\ProxyGateway', $manager->get('Common\GatewayInterface'));
    }

    public function testContains()
    {
        $manager = new Manager();
        $this->assertFalse($manager->contains('Common\Module'));
        $manager->get('Common\Module');
        $this->assertTrue($manager->contains('Common\Module'));
    }

    public function testMethodInjection()
    {
        $m = new Manager;

        $this->assertSame(
            $m->call('Common\Application', 'extractModuleFromManager'), 
            $m->get('Common\Module')
        );

        // numeric array arguments
        $this->assertSame($m->call('Common\Application', 'greet', array('Dmitry')), 'Hello, Dmitry');

        // associative arguments
        $this->assertSame($m->call('Common\Application', 'greet', array('name' => 'Dmitry')), 'Hello, Dmitry');

        $anotherManager = new Manager();
        $anotherModule = $anotherManager->get('Common\Module');

        // access by key
        $this->assertSame(
            $m->call('Common\Application', 'extractModuleFromManager', array('manager' => $anotherManager)), 
            $anotherModule
        );

        // find by class
        $this->assertSame(
            $m->call('Common\Application', 'extractModuleFromManager', array($anotherManager)), 
            $anotherModule
        );
    }

    public function testDuplicateAliasException()
    {
        $this->setExpectedException('Exception');
        
        $manager = new Manager;
        $manager->getConfiguration()->setAlias('app', 'Common\Application');
        $manager->getConfiguration()->setAlias('app', 'Common\Module');
    }

    public function testMethodParamNotFoundException()
    {
        $this->setExpectedException('Exception');
        $m = new Manager;
        $m->call('Common\Application', 'greet');
    }

    public function testConstructorInjection()
    {
        $m = new Manager();
        $this->assertSame($m->get('Common\ClassWithConstructor')->getApplication(), $m->get('Common\Application'));
    }

    public function testInstanceRegistration()
    {
        $m = new Manager;
        $myApplication = new Common\Application;
        $m->register($myApplication);

        $this->assertSame($m->get('Common\Application'), $myApplication);
    }

    public function testRegistrationConflictException()
    {
        $this->setExpectedException('Exception');

        $myApplication = new Common\Application;

        $m = new Manager;
        $m->register($myApplication);
        $m->register($myApplication);
    }

    public function testNoClassException()
    {
        $this->setExpectedException('Exception');
        $m = new Manager;
        $m->get('Unknown_Class');
    }

    public function testEmptyClass()
    {
        $this->setExpectedException('Exception');
        $m = new Manager;
        $m->get('');
    }

    public function testCreateLocalConfiguration()
    {
        $file = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'resources', 'configuration.php'));
        $local = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'resources', 'local.configuration.php'));

        $configuration = new Configuration();
        file_put_contents($file, '<?php' . PHP_EOL . 'return array();');
        if(file_exists($local)) {
            unlink($local);
        }
        $configuration->load($file);
        $this->assertTrue(file_exists($local));
        unlink($local);
    }

    public function testReferenceRestore()
    {
        $r = Reference::__set_state(array('class' => 'Test'));
        $this->assertInstanceOf("Cti\\Di\\Reference", $r);
        $this->assertSame($r->getClass(), 'Test');

    }
}
