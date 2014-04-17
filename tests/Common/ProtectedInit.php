<?php

namespace Common;

class ProtectedInit
{
    public $inited = false;
    
    protected function init()
    {
        $this->inited = true;
    }
}