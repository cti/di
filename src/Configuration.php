<?php

namespace Cti\Di;

/**
 * Class Configuration
 * @package Cti\Di
 */
class Configuration
{
    /**
     * @var array
     */
    protected $alias;

    /**
     * @var object
     */
    protected $data;

    /**
     * @param array $data
     */
    function __construct($data = array())
    {
        foreach($data as $k => $v) {
            if(is_string($v)) {
                $this->alias[$k] = $v;
            } else {
                $this->data[$k] = $v;
            }
        }
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
     * append value to an array
     * @param string $class
     * @param string $property
     * @param mixed  $default
     * @return null
     */
    public function push($class, $property, $value, $key = null)
    {
        if(!isset($this->data[$class])) {
            $this->data[$class] = array();
        }

        if(!isset($this->data[$class][$property])) {
            $this->data[$class][$property] = array();
        } 

        if(!is_array($this->data[$class][$property])) {
            throw new Exception(sprintf("Can't push to %s property", gettype($this->data[$class][$property])));
        }

        if(!$key) {
            $this->data[$class][$property][] = $value;

        } else {
            if(isset($this->data[$class][$property][$key])) {
                throw new Exception(sprintf("Key %s of property %s was already set", $key, $property));
            }
            $this->data[$class][$property][$key] = $value;
        }
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

    public function load($config)
    {
        if(is_array($config)) {
            $this->merge($config);
            return true;
        } 

        if(file_exists($config)) {
            $this->merge(include $config);
            $local = dirname($config) . DIRECTORY_SEPARATOR . 'local.' . basename($config);
            if(file_exists($local)) {
                $this->merge(include $local);
            } else {
                file_put_contents($local, '<?php' . PHP_EOL . PHP_EOL . 'return array(' . PHP_EOL . ');');
            }

            return true;
        }

        throw new Exception(sprintf("Error processing application configuration: %s", $params['config']));
    }

    /**
     * @param string $source
     */
    public function hasAlias($source)
    {
        return isset($this->alias[$source]);
    }

    /**
     * @param string $source
     * @param string $destination
     * @return Cti\Di\Configuration
     */
    public function setAlias($source, $destination)
    {
        if(isset($this->alias[$source])) {
            throw new Exception(sprintf("Alias %s is already registered", $source));
        }
        $this->alias[$source] = $destination;
        return $this;
    }

    /**
     * @param string $source
     * @return string
     */
    public function getAlias($source)
    {
        return $this->alias[$source];
    }

}