<?php

namespace Inject;

use Inject\FakeNamespace as QA;

class ByNamespace {
    /**
     * @inject
     * @var QA\FakeClass
     */
    public $fakeClass;
} 