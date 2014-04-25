<?php

namespace Inject;

use Inject\FakeNamespace\FakeClass;

class ShortClassName {
    /**
     * @inject
     * @var FakeClass
     */
    public $fakeClass;
} 