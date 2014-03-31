<?php

namespace Base\Di;

/**
 * Class Manager
 * @package Base\Di
 */
class Manager
{
    /**
     * @var Base\Di\Configuration
     */
    protected $config;

    /**
     * @var array
     */
    protected $instance = array();

    /**
     * @var array
     */
    protected $callback = array();

    /**
     * @param Configuration $config
     */
    public function __construct(Configuration $config = null)
    {
        if (!$config) {
            $config = new Configuration;
        }
        $this->register($this);
        $this->register($this->config = $config);
    }

    /**
     * @param string $class
     * @return mixed
     * @throws Exception
     */
    public function get($class)
    {
        if (!$class) {
            throw new Exception();
        }
        if (!isset($this->instance[$class])) {
            $this->instance[$class] = $this->createInstance($class);

            if (method_exists($class, 'init')) {
                $this->call($this->instance[$class], 'init');
            }
        }
        return $this->instance[$class];
    }

    /**
     * @param string $class
     * @param array $config
     * @return object
     */
    public function create($class, $config = array())
    {

        $instance = $this->createInstance($class, $config);

        if (method_exists($class, 'init')) {
            $this->call($instance, 'init');
        }

        return $instance;
    }

    protected function createInstance($class, $config = array())
    {
        $configuration = $this->config->get($class);
        $parameters = array_merge($configuration, $config);

        if(!class_exists($class)) {
            throw new Exception("Class $class not found!");
        }

        if (!method_exists($class, '__construct')) {
            $instance = new $class;

        } else {
            // define complete params
            $parameters['config'] = $parameters;

            // launch with null instance, cause it's constructor
            $callback = $this->getCallback($class, '__construct');
            $instance = $callback->launch(null, $parameters, $this);
        }

        foreach ($parameters as $k => $v) {
            if (property_exists($class, $k)) {
                if (Reflection::getReflectionProperty($class, $k)->isPublic()) {
                    $instance->$k = $v;
                }
            }
        }

        foreach(Reflection::getReflectionClass($class)->getProperties() as $property) {
            if(stristr($property->getDocComment(), '@inject')) {
                foreach(explode("\n", $property->getDocComment()) as $line) {
                    if(stristr($line, '@var')) {

                        foreach(explode(' ', substr($line, stripos($line, '@var') + 4)) as $item) {
                            if(strlen($item) > 0) {
                                if($item[0] == '\\') {
                                    $item = substr($item, 1);
                                }
                                $injected_class = trim(str_replace("\r", '', $item));
                                break;
                            }
                        }
                    }
                }

                if(!$property->isPublic()) {
                    $property->setAccessible(true);
                }

                $property->setValue($instance, $this->get($injected_class));

                if(!$property->isPublic()) {
                    $property->setAccessible(false);
                }
            }
        }

        return $instance;
    }

    /**
     * @param mixed $object 
     */
    public function register($object, $class = null)
    {
        if(!$class) {
            $class = get_class($object);
        }
        if(isset($this->instance[$class])) {
            throw new Exception("Error Injecting $class");
        }
        $this->instance[$class] = $object;
    }

    /**
     * @param string $class
     */
    public function contains($class)
    {
        return isset($this->instance[$class]);
    }

    /**
     * @param mixed  $instance
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function call($instance, $method, $arguments = array())
    {
        if (!is_object($instance)) {
            $class = $instance;
            $instance = $this->get($instance);
        } else {
            $class = get_class($instance);
        }
        $callback = $this->getCallback($class, $method);
        return $callback->launch($instance, $arguments, $this);
    }

    /**
     * @param string $class
     * @param string $method
     * @return Callback
     */
    protected function getCallback($class, $method)
    {
        $key = $class . '.' . $method;
        if (!isset($this->callback[$key])) {
            $this->callback[$key] = new Callback($class, $method);
        }
        return $this->callback[$key];
    }
}