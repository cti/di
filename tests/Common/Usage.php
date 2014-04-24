<?php

namespace Common;

use Cti\Di\Manager;
use Cti\Di\Configuration as Config;

class Usage
{
    /**
     * @inject
     * @var Manager
     */
    public $manager;

    /**
     * @inject
     * @var Config
     */
    public $config;
}