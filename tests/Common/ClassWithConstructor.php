<?php

namespace Common;

class ClassWithConstructor
{
    public $property;
    
    protected $application;

    function __construct(Application $application) 
    {
        $this->application = $application;
    }

    public function getApplication() 
    {
        return $this->application;
    }
    
}