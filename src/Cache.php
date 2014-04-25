<?php

namespace Cti\Di;

/**
 * Class Cache
 * @package Cti\Di
 */
class Cache
{
    /**
     * cache container
     * @var array
     */
    private $data = array();

    /**
     * get unique key for given parameters
     * @param string $method
     * @param array $arguments
     * @return string
     */
    protected function getKey($method, $arguments)
    {
        return md5($method . ' ' . implode(' ', $arguments));
    }

    /**
     * get values from cache
     * @param string $method
     * @param array $arguments
     * @return mixed|bull
     */
    function get($method, $arguments)
    {
        $key = $this->getKey($method, $arguments);
        if(isset($this->data[$key])) {
            return $this->data[$key];
        }
    }

    /**
     * set cache value
     * @param string $method
     * @param array $arguments
     * @param mixed $result
     */
    function set($method, $arguments, $result)
    {
        $key = $this->getKey($method, $arguments);
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
}