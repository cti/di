<?php

namespace Cti\Di;

/**
 * Class Manager
 * @package Cti\Di
 */
class Manager
{
    /**
     * @var Configuration
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
        $this->register(new Cache);
    }

    /**
     * switch locator service integration
     * @param boolean $flag
     * @return Manager
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
     * @return Manager
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
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * @param string $source
     * @param string $destination
     * @return Manager
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
        if ($this->config->hasAlias($class)) {
            return $this->get($this->config->getAlias($class));
        }
        if (!$class) {
            throw new Exception();
        }
        if (!isset($this->instance[$class])) {

            $instance = null;

            if ($this->enableServiceLookup && isset($this->instance['Cti\\Di\\Locator'])) {
                $locator = $this->instance['Cti\\Di\\Locator'];
                $instance = $locator->findByClass($class);
            }

            $this->instance[$class] = $instance ? $instance : $this->createInstance($class);

            if (!$instance) {
                $this->getInitializer()->process($this->instance[$class]);
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
        if ($this->config->hasAlias($class)) {
            return $this->create($this->config->getAlias($class));
        }

        $instance = $this->createInstance($class, $config);
        $this->getInitializer()->process($instance);
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

        if ($this->enableServiceLookup && isset($this->instance['Cti\\Di\\Locator'])) {
            $locator = $this->instance['Cti\\Di\\Locator'];
            foreach ($parameters as $name => $value) {
                if (is_string($value) && $value[0] == '@') {
                    if ($value[1] == '@') {
                        $parameters[$name] = substr($value, 1);
                    } else {
                        $parameters[$name] = $locator->get(substr($value, 1));
                    }
                }
            }
        }

        if (!class_exists($class)) {
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

        if ($class == 'Cti\\Di\\Inspector') {
            $instance->cache = $this->get('Cti\\Di\\Cache');

        } else {
            $inspector = $this->getInspector();

            // injection contains class injection
            $injection = array();
            foreach ($inspector->getClassInjection($class) as $name => $value) {
                $injection[$name] = $this->get($value);
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
                if ($this->configureAllProperties) {
                    $reflection = Reflection::getReflectionProperty($class, $name);
                    $reflection->setAccessible(true);
                    $reflection->setValue($instance, $value);
                    $reflection->setAccessible(false);
                }
            }
        }

        return $instance;
    }

    /**
     * @param mixed $object
     * @return Manager
     */
    public function register($object, $class = null)
    {
        if (!$class) {
            $class = get_class($object);
        }
        if (isset($this->instance[$class])) {
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
     * @param mixed $instance
     * @param string $method
     * @param array $arguments
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
            $this->callback[$key] = new Callback($this, $class, $method);
        }
        return $this->callback[$key];
    }

    /**
     * @return Inspector
     */
    public function getInspector()
    {
        return $this->get('Cti\\Di\\Inspector');
    }

    /**
     * @return Initializer
     */
    public function getInitializer()
    {
        return $this->get('Cti\\Di\\Initializer');
    }
}
