<?php

namespace Cti\Di;

/**
 * Class Callback
 * @package Cti\Di
 */
class Callback
{

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $arguments = array();

    /**
     * @var int
     */
    protected $requiredCount = 0;

    /**
     * @param string $class
     * @param string $method
     */
    function __construct(Manager $manager, $class, $method)
    {
        $this->class = $class;
        $this->method = $method;

        $inspector = $manager->get('Cti\\Di\\Inspector');

        $this->arguments = $inspector->getMethodArguments($class, $method);
        $this->requiredCount = $inspector->getMethodRequiredCount($class, $method);
    }

    /**
     * @param null $instance
     * @param array $parameters
     * @param Manager $manager
     * @return mixed
     * @throws Exception
     */
    function launch($instance = null, $parameters, Manager $manager)
    {
        $arguments = array();
        foreach ($this->arguments as $index => $argument) {
            if(is_string($argument) && isset($parameters[$argument])) {
                $arguments[] = $parameters[$argument];

            } elseif ($argument instanceof Reference) {

                // find parameter by class
                $foundInParams = false;
                foreach($parameters as $param) {
                    if(is_a($param, $argument->getClass())) {
                        $arguments[] = $param;
                        $foundInParams = true;
                        break;
                    }
                }

                if(!$foundInParams) {
                    $arguments[] = $argument->getInstance($manager);
                }

            } else {
                if (count(array_filter(array_keys($parameters), 'is_int')) > 0) {
                    $arguments[] = array_shift($parameters);
                } else {
                    if ($index < $this->requiredCount) {
                        throw new Exception(sprintf("Key %s for method %s::%s not found!", $argument, $this->class, $this->method));
                    }
                }
            }
        }

        if ($this->method == '__construct') {
            if(!count($arguments)) {
                return new $this->class;
            }
            return Reflection::getReflectionClass($this->class)->newInstanceArgs($arguments);
        }

        if(in_array($this->method, $manager->getInspector()->getPublicMethods($this->class))) {
            return call_user_func_array(array($instance, $this->method), $arguments);
        }

        return Reflection::getReflectionMethod($this->class, $this->method)->invokeArgs($instance, $arguments);
    }
}