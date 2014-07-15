<?php

namespace Cti\Di;

use SplObjectStorage;

/**
 * Class Injector
 * @package Cti\Di
 */
class Injector
{
    /**
     * @var Manager
     */
    public $manager;

    public $references;

    function __construct()
    {
        $this->references = new SplObjectStorage;
    }

    function process($instance, $parameters)
    {
        $class = get_class($instance);

        $inspector = $this->getInspector();

        // injection contains class injection
        $injection = array();

        foreach ($inspector->getClassInjection($class) as $name => $inject) {
            if($inject['new']) {
                $injection[$name] = $this->getManager()->create($inject['class']);
            } else {
                $injection[$name] = $this->getManager()->get($inject['class']);
            }

            $list =  array();
            if($this->references->offsetExists($injection[$name])) {
                $list = $this->references->offsetGet($injection[$name]);
            }

            $list[] = array(
                'instance' => $instance,
                'property' => $name
            );

            $this->references->offsetSet($injection[$name], $list);
        }

        $properties = $inspector->getClassProperties($class);

        foreach ($parameters as $name => $value) {
            if (isset($properties[$name])) {
                $injection[$name] = $value;
            }
        }

        foreach ($injection as $name => $value) {
            // public property
            if ($properties[$name]) {
                $instance->$name = $value;
                continue;
            }

            // protected property
            if ($this->getManager()->getConfigureAllProperties()) {
                $reflection = Reflection::getReflectionProperty($class, $name);
                $reflection->setAccessible(true);
                $reflection->setValue($instance, $value);
                $reflection->setAccessible(false);
            }
        }
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return Inspector
     */
    public function getInspector()
    {
        return $this->getManager()->getInspector();
    }

    public function getReferences($instance)
    {
        return isset($this->references[$instance]) ? $this->references[$instance] : array();
    }

} 