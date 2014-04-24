<?php

namespace Cti\Di;

use ReflectionClass;
use SplObjectStorage;

/**
 * Class Parser
 * @package Cti\Di
 */
class Parser
{
    /**
     * @var SplObjectStorage
     */
    protected $cache;

    /**
     * initialize cache property
     */
    function __construct()
    {
        $this->cache = new SplObjectStorage();
    }

    /**
     * get usage hash
     * @param ReflectionClass $class
     * @return mixed
     */
    function getUsage(ReflectionClass $class)
    {
        if(!isset($this->cache[$class])) {
            $this->cache[$class] = array();
            foreach(file($class->getFileName()) as $line) {
                if(strpos($line, 'use ') === 0) {
                    $line = substr($line, 0, strpos($line, ';'));
                    $chain = array_filter(explode(' ', $line), 'strlen');
                    if(strpos($line, ' as ')) {
                        $destination = $chain[1];
                        $alias = $chain[3];
                    } else {
                        $destination = $chain[1];
                        $alias = Reflection::getReflectionClass($chain[1])->getShortName();
                    }
                    $result[$alias] = $destination;
                }
                if(strpos($line, 'class ') === 0) {
                    break;
                }
            }
            $this->cache[$class] = $result;
        }
        return $this->cache[$class];
    }
}