<?php

namespace Cti\Di;

/**
 * Class Cache
 * @package Cti\Di
 */
class Cache
{
    /**
     * @inject
     * @var Manager
     */
    public $manager;

    /**
     * @var bool
     */
    public $debug;

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
        if(is_null($this->debug)) {
            $this->debug = $this->manager->getConfiguration()->get(__CLASS__, 'debug', false);
        }
        $key = $method . '(' . implode(', ', $arguments) . ')';
        return $this->debug ? $key : md5($key);
    }

    /**
     * get values from cache
     * @param string $method
     * @param array $arguments
     * @return mixed|bull
     */
    function get($method, $arguments)
    {
        if($this->contains($method, $arguments)) {
            $key = $this->getKey($method, $arguments);
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

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function contains($method, $arguments)
    {
        $key = $this->getKey($method, $arguments);
        return isset($this->data[$key]);
    }
}