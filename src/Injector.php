<?php

namespace Cti\Di;

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

    function process($instance, $parameters)
    {
        $class = get_class($instance);

        $inspector = $this->getInspector();

        // injection contains class injection
        $injection = array();

        foreach ($inspector->getClassInjection($class) as $name => $value) {
            $injection[$name] = $this->getManager()->get($value);
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

} 