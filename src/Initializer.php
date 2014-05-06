<?php

namespace Cti\Di;

class Initializer
{
    /**
     * @inject
     * @var Manager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $before = array();

    /**
     * @var array
     */
    protected $after = array();

    /**
     * @param $instance
     */
    function process($instance)
    {
        $this->processHook($this->before, $instance);

        $class = get_class($instance);

        if (method_exists($instance, 'init')) {
            if (in_array('init', $this->getManager()->getInspector()->getPublicMethods($class))) {
                $this->getManager()->call($instance, 'init');

            } else {
                $reflection = Reflection::getReflectionMethod($class, 'init');
                $reflection->setAccessible(true);
                $this->getManager()->call($instance, 'init');
                $reflection->setAccessible(false);
            }
        }

        $this->processHook($this->after, $instance);
    }

    public function processHook($array, $instance)
    {
        $class = get_class($instance);

        if(isset($array[$class])) {
            foreach($array[$class] as $callback) {
                if(is_array($callback)) {
                    list($class, $method) = $callback;
                    $this->getManager()->call($class, $method, array($instance));
                } else {
                    call_user_func($callback, $instance);
                }
            }
        }
    }

    /**
     * register before
     * @param $class
     * @param $callback
     */
    public function before($class, $callback)
    {
        if (!isset($this->before[$class])) {
            $this->before[$class] = array();
        }
        $this->before[$class][] = $callback;
    }

    /**
     * register after
     * @param $class
     * @param $callback
     */
    public function after($class, $callback)
    {
        if (!isset($this->after[$class])) {
            $this->after[$class] = array();
        }
        $this->after[$class][] = $callback;
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }
}