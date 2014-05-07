<?php

use Cti\Di\Manager;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function testDebug()
    {
        $configuration = new \Cti\Di\Configuration(array(
            'Cti\\Di\\Cache' => array(
                'debug' => true
            )
        ));
        $manager = new Manager($configuration);

        $this->assertTrue($manager->getCache()->debug);
    }
    public function testCache()
    {
        $manager = new Manager();

        $startTime = microtime(1);
        $manager->get('Inject\FullClassName');
        $manager->get('Inject\ShortClassName');
        $manager->get('Inject\ByNamespace');
        $manager->call('Common\Application', 'extractModuleFromManager');
        $parsingTime = microtime(1) - $startTime;

        $data = $manager->get('Cti\Di\Cache')->getData();
        $manager = new Manager();
        $manager->get('Cti\Di\Cache')->setData($data);

        $startTime = microtime(1);
        $manager->get('Inject\FullClassName');
        $manager->get('Inject\ShortClassName');
        $manager->get('Inject\ByNamespace');
        $manager->call('Common\Application', 'extractModuleFromManager');
        $cachedTime = microtime(1) - $startTime;

        $this->assertGreaterThan($cachedTime, $parsingTime);

        $this->assertNull($manager->get('Cti\Di\Cache')->get(2,array(3)));
    }
} 