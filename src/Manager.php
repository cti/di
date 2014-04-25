<?php

namespace Cti\Di;

/**
 * Class Manager
 * @package Cti\Di
 */
class Manager
{
    /**
     * @var \Cti\Di\Configuration
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
     * service lookup flag
     * @var float
     */
    protected $enableServiceLookup = true;

    /**
     * configure all properties
     * @var float
     */
    protected $configureAllProperties = true;

    /**
     * @param Configuration|null $config
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
     * switch locator service integration
     * @param boolean $flag
     * @return \Cti\Di\Manager
     */
    function setServiceLookup($value)
    {
        $this->enableServiceLookup = $value;
        return $this;
    }

    /**
     * @return  boolean
     */
    function getServiceLookup()
    {
        return $this->enableServiceLookup;
    }
    
    /**
     * switch configure properties flah
     * @param boolean $flag
     * @return \Cti\Di\Manager
     */
    function setConfigureAllProperties($value) 
    {
        $this->configureAllProperties = $value;
    }
    
    /**
     * @return  boolean
     */
    function getConfigureAllProperties()
    {
        return $this->configureAllProperties;
    }

    /**
     * @return \Cti\Di\Configuration
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * @param string $source
     * @param string $destination
     * @return \Cti\Di\Manager
     */
    public function setAlias($source, $destination)
    {
        $this->config->setAlias($source, $destination);
        return $this;
    }

    /**
     * @param string $class
     * @return mixed
     * @throws Exception
     */
    public function get($class)
    {
        if($this->config->hasAlias($class)) {
            return $this->get($this->config->getAlias($class));
        }
        if (!$class) {
            throw new Exception();
        }
        if (!isset($this->instance[$class])) {

            $instance = null;

            if($this->enableServiceLookup && isset($this->instance['Cti\Di\Locator'])) {
                $locator = $this->instance['Cti\Di\Locator'];
                $instance = $locator->findByClass($class);
            }

            $this->instance[$class] = $instance ? $instance : $this->createInstance($class);

            if (!$instance && method_exists($class, 'init')) {
                $reflection = Reflection::getReflectionMethod($class, 'init');
                if($reflection->isProtected()) {
                    $reflection->setAccessible(true);
                }
                $this->call($this->instance[$class], 'init');
                if($reflection->isProtected()) {
                    $reflection->setAccessible(false);
                }
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
        if($this->config->hasAlias($class)) {
            return $this->create($this->config->getAlias($class));
        }

        $instance = $this->createInstance($class, $config);

        if (method_exists($class, 'init')) {
            $this->call($instance, 'init');
        }

        return $instance;
    }

    /**
     * create instance with given configuration
     * @param string $class
     * @param array $config
     * @return mixed
     * @throws Exception
     */
    protected function createInstance($class, $config = array())
    {
        $configuration = $this->config->get($class);
        $parameters = array_merge($configuration, $config);

        if($this->enableServiceLookup && isset($this->instance['Cti\Di\Locator'])) {
            $locator = $this->instance['Cti\Di\Locator'];
            foreach($parameters as $k => $v) {
                if(is_string($v) && $v[0] == '@') {
                    if($v[1] == '@') {
                        $parameters[$k] = substr($v, 1);
                    } else {
                        $parameters[$k] = $locator->get(substr($v, 1));
                    }
                }
            }
        }

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
                $reflection = Reflection::getReflectionProperty($class, $k);
                if ($this->configureAllProperties || $reflection->isPublic()) {
                    if(!$reflection->isPublic()) {
                        $reflection->setAccessible(true);
                    }
                    $reflection->setValue($instance, $v);
                    if(!$reflection->isPublic()) {
                        $reflection->setAccessible(false);
                    }
                }
            }
        }

        $reflectionClass = Reflection::getReflectionClass($class);
        foreach($reflectionClass->getProperties() as $property) {
            if(stristr($property->getDocComment(), '@inject')) {
                foreach(explode("\n", $property->getDocComment()) as $line) {
                    if(stristr($line, '@var')) {

                        foreach(explode(' ', substr($line, stripos($line, '@var') + 4)) as $item) {
                            if(strlen($item) > 0) {
                                $global = false;
                                if($item[0] == '\\') {
                                    $global = true;
                                    $item = substr($item, 1);
                                }
                                $injected_class = trim(str_replace("\r", '', $item));

                                if(!$global) {
                                    /**
                                     * @var Parser $parser
                                     */
                                    $parser = $this->get('Cti\Di\Parser');
                                    $aliases = $parser->getUsage($reflectionClass);
                                    if(isset($aliases[$injected_class])) {
                                        // imported with use statement
                                        $injected_class = $aliases[$injected_class];
                                        
                                    } else {
                                        // from class namespace
                                        $injected_class = $reflectionClass->getNamespaceName() . '\\' . $injected_class;
                                    }

                                }
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
     * @return \Cti\Di\Manager
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
        return $this;
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
