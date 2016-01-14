# Initializer

Initializer used by manager to initalize object (call init method if it exists).  
You can register hooks before / after initialization.

```php
<?php

class Test
{
    public $classes = array();

    public init()
    {
        foreach($this->classes as $class) {
            echo $class, PHP_EOL;
        }
    }
}

$manager = new Manager;
$manager->getInitializer()->before('Test', function($test) {
   $test->classes[] = 'Cti\Di\Manager';
});

$manager->get('Test'); // echo Cti\Di\Manager;
```

Of course you can add handler before/after. Handler can be object or class calback:
```php
<?php

class Extension
{
    function extend(Application $application, Manager $manager)
    {
         $application->modules[] = $manager->get('Monolog\Logger');
    }
}

class Application
{
    public $modules = array();
}

$manager = new Manager();
$manager->getInitializer()->after('Application', array('Extension', 'extend'));
// or
// $extension = new Extension();
// $manager->getInitializer()->after('Application', array($extension, 'extend'));
