<?php

namespace Common;

class Module
{
    public $state;
    public $new_state;

    public function getState() 
    {
        return $this->state;
    }

    public function init()
    {
        $this->new_state = $this->state.'!';
    }

}