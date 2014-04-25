<?php

namespace Inject;

use Inject\FakeNamespace;

class ByNamespace {
    /**
     * @inject
     * @var FakeNamespace\FakeClass;
     */
    public $fakeClass;
} 