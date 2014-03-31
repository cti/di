<?php

namespace Base\Di;

use RangeException;

/**
 * Class Configuration
 * @package Base\Di
 */
class Configuration
{
    /**
     * @var object
     */
    protected $data;

    /**
     * @param array $data
     */
    function __construct($data = null)
    {
        $this->data = $data;
    }

    /**
     * @param string $class 
     * @param string $property
     * @param string $value
     */
    public function set($class, $property, $value)
    {
        if(!isset($this->data[$class])) {
            $this->data[$class] = array();
        }
        $this->data[$class][$property] = $value;
    }

    /**
     * @param string $class
     * @param string $property
     * @param mixed  $default
     * @return mixed
     */
    public function get($class, $property = null, $default = null) 
    {
        if(!$property) {
            return isset($this->data[$class]) ? $this->data[$class] : array();
        }
        return isset($this->data[$class][$property]) ? $this->data[$class][$property] : $default;
    }

    /**
     * @param array $data
     */
    public function merge($data)
    {
        foreach($data as $class => $config) {
            if(!isset($this->data[$class])) {
                $this->data[$class] = $config;
            } else {
                foreach($config as $k => $v) {
                    $this->data[$class][$k] = $v;
                }
            }
        }
    }

}