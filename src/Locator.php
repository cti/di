<?php

namespace Cti\Di;

class Locator
{
    protected $instances = array();

    protected $definition = array(
        'manager' => array(
            'class' => 'Cti\Di\Manager',
            'configuration' => array()
        )
    );

    protected $methods = array();
    protected $classes = array();

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

    function __call($method, $aguments)
    {
        if(!isset($this->methods[$method])) {
            foreach (array_keys($this->definition) as $service) {
                $name = 'get' . $this->camelCaseServiceName($service);
                $this->methods[$name] = $service;
            }
        }
        if(!isset($this->methods[$method])) {
            throw new Exception(sprintf("Error processing getter - %s", $method));
        }
        return $this->get($this->methods[$method]);
    }

    function camelCaseServiceName($string)
    {
        foreach(array('.', '_', '-') as $delimiter) {
            if(strstr($string, $delimiter)) {
                return implode('', array_map('ucfirst', explode($delimiter, $string)));
            }
        }
        return ucfirst($string);
    }

    function findByClass($class)
    {
        if(!isset($this->classes[$class])) {
            foreach($this->definition as $name => $config) {
                if(isset($config['class'])) {
                    if(!isset($this->classes[$config['class']])) {
                        $this->classes[$config['class']] = array();
                    } 
                    if(!in_array($name, $this->classes[$config['class']])) {
                        $this->classes[$config['class']][] = $name;
                    }
                }
            }

        }

        if(!isset($this->classes[$class])) {
            $this->classes[$class] = array();
        }

        if(count($this->classes[$class]) == 1) {
            return $this->get($this->classes[$class][0]);
        }
        return null;
    }

    function get($name)
    {
        if(isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        if($name == 'manager') {
            $this->instances[$name] = new Manager;
            $this->instances[$name]->register($this);
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

        $manager = $this->get('manager');

        $serviceLookup = $manager->getServiceLookup();

        $manager->setServiceLookup(true);
        $this->instances[$name] = $manager->create($definition['class'], $configuration);
        $manager->setServiceLookup($serviceLookup);
        
        return $this->instances[$name];
    }

    function register($name, $config)
    {
        $this->classes = array();

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