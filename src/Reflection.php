<?php

namespace Base\Di;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Class Reflection
 * @package Base\Di
 */
abstract class Reflection
{

    /**
     * @param string $class
     * @return ReflectionClass
     */
    public static function getReflectionClass($class)
    {
        static $instances = array();
        if (!isset($instances[$class])) {
            $instances[$class] = new ReflectionClass($class);
        }
        return $instances[$class];
    }

    /**
     * @param string $class
     * @param string $property
     * @return ReflectionProperty
     */ 
   public static function getReflectionProperty($class, $property)
    {
        static $instances = array();
        $key = $class . '.' . $property;
        if (!isset($instances[$key])) {
            $instances[$key] = new ReflectionProperty($class, $property);
        }
        return $instances[$key];
    }

    /**
     * @param string $class
     * @param string $method
     * @return ReflectionMethod
     */
    public static function getReflectionMethod($class, $method)
    {
        static $instances = array();
        $key = $class . '.' . $method;
        if (!isset($instances[$key])) {
            $instances[$key] = new ReflectionMethod($class, $method);
        }
        return $instances[$key];
    }

} 