<?php

use Cti\Di\Manager;

class CacheTest extends \PHPUnit_Framework_TestCase
{
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
    }
} 