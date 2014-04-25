<?php

namespace Inject;

class FullClassName {
    /**
     * @inject
     * @var \Inject\FakeNamespace\FakeClass
     */
    public $fakeClass;

    /**
     * @var \Inject\ByNamespace
     */
    public $namespace;
} 