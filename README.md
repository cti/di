# Dependency Manager
This component implements dependency injection pattern.   
Manager can inject properties, configure objects and resolve depenencies while calling methods.  

# Object configuration
Configuration param is optional, but it is very useful for configure instances.

```php
<?php

use Base\Di\Configuration;
use Base\Di\Manager;

$configuration = new Configuration(array(
    'ClassName' => array(
        'property' => 'value'
    )
));

$manager = new Manager($configuration);

// class property was set while creating instance
$manager->create('ClassName')->property; // value

```

You can merge configuration from different files and set properties directly.

```php
<?php

use Base\Di\Configuration;

$configuration = new Configuration();

// override one property is easy
$configuration->set('Database', 'hostname', '192.168.2.87');

// override multiple properties
$configuration->merge(array(
    'Database' => array(
        'username' => 'nekufa',
        'password' => 'secret',
        'hostname' => '192.168.2.91',
    )
));

// get full class configuration as array
$configuration->get('Database');

// or any property
$configuration->get('Database', 'username');

```

# Property injection
One of usefull scenario is to inject dependencies when object is created.  
Injection works recursive, so if module requires another dependency - it would be resolved. 

```php
<?php

use Base\Di\Configuration;
use Base\Di\Manager;

class Application
{
    /**
     * @inject
     * @var  Module
     */
    protected $module;

    public function init()
    {
        echo "Application works in module.state = " . $this->module->state;
    }
}

class Module
{
    public $state;
}

$manager = new Manager();

// change class configuration
$manager->get('Base\Di\Configuration')->set('Module', 'state', 'active');


// create Module, set state property, create Application, inject module and call init
$manager->get('Application');

```

# Method caller
One of the killer feature is ability resolve dependencies for method calling.

```php
<?php

use Base\Di\Manager;

class Transport 
{
    function send($message) 
    {
        // ...
    }
}

class Mailer 
{
    function send(Transport $transport, $message) 
    {
        $transport->send($message);
    }
}

$manager = new Manager();

// create Mailer instance and analyze send method
// instantiate Transport (it is dependency) and call send method
$manager->call('Mailer', 'send', array('message' => 'Hello world!');

// numeric array can be used to
$manager->call('Mailer', 'send', array('Hello world!');

```