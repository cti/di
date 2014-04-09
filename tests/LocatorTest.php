<?php

use Cti\Di\Locator;

class LocatorTest extends PHPUnit_Framework_TestCase
{

    function testFailRegistration()
    {
        $this->setExpectedException('Exception');
        $locator = new Locator();
        $locator->parse(array(
            'nullable' => null
        ));
    }

    function testFailArrayRegistration()
    {
        $this->setExpectedException('Exception');
        $locator = new Locator();
        $locator->parse(array(
            'nullable' => array()
        ));
    }

    function testFailParsing()
    {
        $this->setExpectedException('Exception');
        $locator = new Locator;
        $locator->load('?');
    }
    
    function testFileLoading()
    {
        $locator = new Locator;
        $locator->load(__DIR__ . '/resources/services.php');
        $this->assertInstanceOf('Common\Module', $locator->get('module'));
    }


    function testFilesystemLoading()
    {
        $locator = new Locator;
        $locator->load(__DIR__ . '/resources/services.php');
        $this->assertInstanceOf('Common\Module', $locator->get('module'));
    }

    function testLocator()
    {
        $services = array(

            'base' => 'Common\Module',

            'base2' => array('Common\Module', array(
                'state' => 'zzzz'
            )),

            'base3' => array(
                'class' => 'Common\Module',
                'config' => array(
                    'state' => 'q',
                ),
            ),

            'base3x' => array(
                'class' => 'Common\Module',
                'configuration' => array(
                    'state' => 'q',
                ),
            ),

            'base4' => function() {
                return new Common\Module;
            },

            'base5' => array(
                'Common\Module',
                'state' => 'hm'
            ),

            'base6' => array(
                'class' => 'Common\Module', 
                'state' => 'wtf'
            ),

            'base7' => array(
                'class' => 'Common\Module',
                'reference' => '@base6'
            )
        );

        $locator = new Locator();
        $locator->load($services);

        $this->assertInstanceOf('Common\Module', $locator->get('base'));
        $this->assertInstanceOf('Common\Module', $locator->get('base2'));
        $this->assertInstanceOf('Common\Module', $locator->get('base3'));
        $this->assertInstanceOf('Common\Module', $locator->get('base4'));

        $this->assertSame($locator->get('base4'), $locator->get('base4'));

        $this->assertSame($locator->get('base2')->state, 'zzzz');
        $this->assertSame($locator->get('base3')->state, 'q');
        $this->assertSame($locator->get('base3x')->state, 'q');
        $this->assertSame($locator->get('base5')->state, 'hm');
        $this->assertSame($locator->get('base6')->state, 'wtf');

        $this->assertSame($locator->get('base6'), $locator->get('base7')->reference);

        $app = new Common\Application;
        $locator->register('app', $app);
        $this->assertSame($app, $locator->get('app'));

        $this->setExpectedException('Exception');
        $locator->get('no-base');
    }
}