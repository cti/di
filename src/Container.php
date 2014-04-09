<?php

namespace Cti\Di;

class Container
{
    protected $configuration;
    
    protected $locator;

    protected $manager;

    function __construct($params = array())
    {
        $this->configuration = new Configuration();
        $this->manager = new Manager($this->configuration);
        
        $this->manager->register($this);
        $this->locator = $this->manager->get('Cti\Di\Locator');

        if(isset($params['config'])) {
            $this->processConfig($params['config']);
        }

        if(isset($params['services'])) {
            $this->processServices($params['services']);
        }
    }

    function processConfig($config)
    {
        if(is_array($config)) {
            $this->configuration->merge($config);
            return true;
        } 

        if(file_exists($config)) {
            $this->configuration->merge(include $config);

            $local = dirname($config) . DIRECTORY_SEPARATOR . 'local.' . basename($config);
            if(file_exists($local)) {
                $this->configuration->merge(include $local);
            }

            return true;
        }

        throw new Exception(sprintf("Error processing application configuration: %s", $params['config']));
    }

    function processServices($services)
    {
        if(is_array($services)) {
            $data = $services;
        } elseif(file_exists($services)) {
            $data = include $services;
        } else {
            throw new Exception(sprintf("Error processing services configuration: %s", $params['config']));            
        }
        $this->locator->parse($data);
    }

    function getConfiguration()
    {
        return $this->configuration;
    }

    function getManager()
    {
        return $this->manager;
    }

    function getLocator()
    {
        return $this->locator;
    }
}