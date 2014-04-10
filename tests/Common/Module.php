<?php

namespace Common;

class Module
{
    public $state;
    protected $_state;

    public $new_state;

    public $reference;

    public function getState() 
    {
        return $this->state;
    }

    public function getProtectedState()
    {
        return $this->_state;
    }

    public function init()
    {
        $this->new_state = $this->state.'!';
    }

}