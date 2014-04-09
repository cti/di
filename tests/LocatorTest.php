<?php

use Cti\Di\Container;

class LocatorTest extends PHPUnit_Framework_TestCase
{

    function testFailRegistration()
    {
        $this->setExpectedException('Exception');
        $c = new Container(array(
            'config' => array(),
            'services' => array(
                'nullable' => null
            )
        ));
    }

    function testFailArrayRegistration()
    {
        $this->setExpectedException('Exception');
        $c = new Container(array(
            'config' => array(),
            'services' => array(
                'nullable' => array()
            )
        ));
    }

    function testLocator()
    {
        $container = new Container(array(
            'config' => array(),
            'services' => array(

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
                )
            ),
        ));

        $locator = $container->getLocator();

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

        $app = new Common\Application;
        $locator->register('app', $app);
        $this->assertSame($app, $locator->get('app'));

        $this->setExpectedException('Exception');
        $locator->get('no-base');
    }
}