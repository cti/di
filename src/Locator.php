<?php

namespace Cti\Di;

class Locator
{
    protected $manager;
    protected $instances = array();
    protected $definition = array();

    function __construct($manager = null)
    {
        if(is_null($manager)) {
            $manager = new Manager;
        }
        $this->manager = $manager;
    }

    function load($config)
    {
        if(is_array($config)) {
            $data = $config;
        } elseif(file_exists($config)) {
            $data = include $config;
        } else {
            throw new Exception(sprintf("Error processing locator configuration: %s", $config));            
        }
        $this->parse($data);
    }

    function parse($data)
    {
        foreach($data as $service => $configuration) {
            $this->register($service, $configuration);
        }
    }

    function get($name)
    {
        if(isset($this->instances[$name])) {
            return $this->instances[$name];
        }
        if(!isset($this->definition[$name])) {
            throw new Exception(sprintf("Service %s not defined", $name));
        }

        $definition = $this->definition[$name];

        if(isset($definition['callback'])) {
            return $this->instances[$name] = call_user_func($definition['callback']);
        }

        $configuration = isset($definition['configuration']) ? $definition['configuration'] : array();

        foreach($configuration as $k => $v) {
            if(is_string($v) && $v[0] == '@' && $v[1] != '@') {
                $configuration[$k] = $this->get(substr($v,1));
            }
        }

        return $this->instances[$name] = $this->manager->create($definition['class'], $configuration);
    }

    function register($name, $config)
    {
        if(is_callable($config)) {
            
            $this->definition[$name] = array(
                'callback' => $config
            );

        } elseif(is_object($config)) {
            $this->instances[$name] = $config;
            $this->definition[$name] = array(
                'class' => get_class($config)
            );

        } elseif(is_string($config)) {
            $this->definition[$name] = array(
                'class' => $config
            );

        } elseif(is_array($config)) {

            if(isset($config['class'])) {
                if(isset($config['config'])) {
                    $configuration = $config['config'];

                } elseif(isset($config['configuration'])) {
                    $configuration = $config['configuration'];

                } else {
                    $configuration = $config;
                    unset($configuration['class']);
                }
                $this->definition[$name] = array(
                    'class' => $config['class'],
                    'configuration' => $configuration
                );

            } elseif(isset($config[0])) {
                $class = array_shift($config);
                if(count($config) == 1 && isset($config[0]) && is_array($config[0])) {
                    $configuration = $config[0];
                } else {
                    $configuration = $config;                    
                }

                $this->definition[$name] = array(
                    'class' => $class,
                    'configuration' => $configuration,
                );
                
            } else {
                throw new Exception(sprintf("Error processing service configuration: %s", json_encode($config)));
                
            }
        } else {
            throw new Exception(sprintf("Error processing service configuration: %s", json_encode($config)));
            
        }
    }
}