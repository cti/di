<?php

namespace Base\Di;

/**
 * Class Callback
 * @package Base\Di
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
    function __construct($class, $method)
    {
        $this->class = $class;
        $this->method = $method;

        $reflection = Reflection::getReflectionMethod($class, $method);
        foreach ($reflection->getParameters() as $parameter) {
            if (!$parameter->isDefaultValueAvailable()) {
                $this->requiredCount++;
            }
            if ($parameter->getClass()) {
                $this->arguments[] = new Reference($parameter->getClass()->getName());

            } else {
                $this->arguments[] = $parameter->getName();
            }
        }
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
            if(!is_numeric($index) && isset($parameters[$index])) {
                $arguments[] = $parameters[$index];
            } elseif ($argument instanceof Reference) {

                // find parameter by class
                foreach($parameters as $param) {
                    if(is_a($param, $argument->getClass())) {
                        $arguments[] = $param;
                        break 2;
                    }
                }
                $arguments[] = $argument->getInstance($manager);
            } elseif (isset($parameters[$argument])) {
                $arguments[] = $parameters[$argument];
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
            return Reflection::getReflectionClass($this->class)->newInstanceArgs($arguments);
        }

        return Reflection::getReflectionMethod($this->class, $this->method)->invokeArgs($instance, $arguments);
    }
}