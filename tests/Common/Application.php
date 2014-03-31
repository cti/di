<?php

namespace Common;

use Base\Di\Manager;

class Application
{
    /**
     * @inject
     * @var Common\Module
     */
    protected $module;

    /**
     * test global namespace class
     * @inject
     * @var \Common\Module
     */
    protected $same_module;

    public function getModule()
    {
        return $this->module;
    }

    public function extractModuleFromManager(Manager $manager) 
    {
        return $manager->get('Common\Module');
    }

    public function greet($name) 
    {
        return 'Hello, ' . $name;
    }
}