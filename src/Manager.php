<?php

namespace Cti\Di;

/**
 * Class Manager
 * @package Cti\Di
 */
class Manager
{
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
    protected $serviceLookup = true;

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
        if(!$this->contains(__CLASS__)) {
            $this->register($this, __CLASS__);
        }

        $this->register($config);

        $this->register(new Cache);
    }

    /**
     * switch locator service integration
     * @param $value
     * @internal param bool $flag
     * @return Manager
     */
    function setServiceLookup($value)
    {
        $this->serviceLookup = $value;
        return $this;
    }

    /**
     * @return  boolean
     */
    function getServiceLookup()
    {
        return $this->serviceLookup;
    }

    /**
     * switch configure properties flag
     * @param $value
     * @internal param bool $flag
     * @return Manager
     */
    function setConfigureAllProperties($value)
    {
        $this->configureAllProperties = $value;
    }

    /**
     * @return boolean
     */
    function getConfigureAllProperties()
    {
        return $this->configureAllProperties;
    }

    /**
     * @param string $class
     * @return mixed
     * @throws Exception
     */
    public function get($class)
    {
        if ($this->getConfiguration()->hasAlias($class)) {
            return $this->get($this->getConfiguration()->getAlias($class));
        }

        if (!$class) {
            throw new Exception();
        }

        if (!isset($this->instance[$class])) {

            $instance = null;

            if ($this->getServiceLookup() && $this->hasLocator()) {
                $instance = $this->getLocator()->findByClass($class);
            }

            if ($instance) {
                $this->instance[$class] = $instance;

            } else {
                $this->instance[$class] = $this->createInstance($class);
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
        if ($this->getConfiguration()->hasAlias($class)) {
            return $this->create($this->getConfiguration()->getAlias($class));
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
        $configuration = $this->getConfiguration()->get($class);
        $parameters = array_merge($configuration, $config);

        if ($this->getServiceLookup() && $this->hasLocator()) {
            foreach ($parameters as $name => $value) {
                if (is_string($value) && $value[0] == '@') {
                    if ($value[1] == '@') {
                        $parameters[$name] = substr($value, 1);
                    } else {
                        $parameters[$name] = $this->getLocator()->get(substr($value, 1));
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

        if (in_array($class, array('Cti\\Di\\Inspector', 'Cti\\Di\\Injector'))) {
            // injector not exists, manual manager injection
            $instance->manager = $this;

        } else {
            $this->getInjector()->process($instance, $parameters);
        }

        return $instance;
    }

    /**
     * @param mixed $object
     * @param string $class
     * @throws Exception
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
     * @return bool
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
        $key = $class . '::' . $method;
        if (!isset($this->callback[$key])) {
            $this->callback[$key] = new Callback($this, $class, $method);
        }
        return $this->callback[$key];
    }

    /**
     * @return Cache
     * @throws Exception
     */
    public function getCache()
    {
        return $this->get('Cti\\Di\\Cache');
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->instance['Cti\\Di\\Configuration'];
    }

    /**
     * @return Injector
     * @throws Exception
     */
    public function getInjector()
    {
        return $this->get('Cti\\Di\\Injector');
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

    /**
     * @return Locator
     * @throws Exception
     */
    public function getLocator()
    {
        return $this->get('Cti\\Di\\Locator');
    }

    /**
     * @return bool
     */
    public function hasLocator()
    {
        return isset($this->instance['Cti\\Di\\Locator']);
    }
}
