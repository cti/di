<?php

namespace Cti\Di;

/**
 * Class Cache
 * @package Cti\Di
 */
class Cache
{
    /**
     * @var bool
     */
    public $debug = false;

    /**
     * cache container
     * @var array
     */
    private $data = array();

    /**
     * get unique key for given parameters
     * @param string $class
     * @param string $method
     * @param array $arguments
     * @return string
     */
    protected function getKey($class, $method, $arguments)
    {
        $key = $class . '::' . $method . '(' . implode(', ', $arguments) . ')';
        return $this->debug ? $key : md5($key);
    }

    /**
     * get values from cache
     * @param string $class
     * @param string $method
     * @param array $arguments
     * @return mixed|bull
     */
    function get($class, $method, $arguments)
    {
        if($this->contains($class, $method, $arguments)) {
            $key = $this->getKey($class, $method, $arguments);
            return $this->data[$key];
        }
    }

    /**
     * set cache value
     * @param string $class
     * @param string $method
     * @param array $arguments
     * @param mixed $result
     */
    function set($class, $method, $arguments, $result)
    {
        $key = $this->getKey($class, $method, $arguments);
        $this->data[$key] = $result;
    }

    /**
     * get container data
     * @return array
     */
    function getData()
    {
        return $this->data;
    }

    /**
     * set container data
     * @param array $data
     */
    function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param $class
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function contains($class, $method, $arguments)
    {
        $key = $this->getKey($class, $method, $arguments);
        return isset($this->data[$key]);
    }
}