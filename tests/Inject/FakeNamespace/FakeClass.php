<?php

namespace Inject\FakeNamespace;

class FakeClass {
    public $initialized = false;

    public function __construct()
    {
        $this->initialized = true;
    }

} 