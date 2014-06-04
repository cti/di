# Callback
Callback calculates needed arguments for method calling.  
It is used by Manager, to call with given parameters/dependencies.  

```php
<?php

use Cti\Di\Callback;

use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;

class Test
{
    function method($name, Filesystem $fs, Application $console)
    {}
}

$manager = new Manager;

// manager used for integrate with cache (via inspector)
$callback = new Callback($manager, 'Test', 'method');

// create class instance
$instance = $manager->create('Test');

// call method
// Filesystem and Application would be created by Manager
$callback->launch($instance, array('name' => 'nekufa'), $manager);

// use manager (same as above)
$manager->call($instance, 'method', array('name' => 'nekufa'));

// use manager (create an instance)
$manager->call('Test', 'method', array('name' => 'nekufa'));
```
