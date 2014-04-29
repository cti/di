<?php

namespace Cti\Di;

/**
 * Class Reference
 * @package Cti\Di
 */
class Reference
{

    /**
     * @var string
     */
    protected $class;

    /**
     * @param $class
     */
    function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    function getClass()
    {
        return $this->class;
    }

    /**
     * @param Manager $manager
     * @return object
     */
    function getInstance(Manager $manager)
    {
        return $manager->get($this->class);
    }

    public static function __set_state($data)
    {
        return new self($data['class']);
    }
}