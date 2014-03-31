# Dependency Manager

[![Latest Stable Version](https://poser.pugx.org/nekufa/di/v/stable.png)](https://packagist.org/packages/nekufa/di)
[![Total Downloads](https://poser.pugx.org/nekufa/di/downloads.png)](https://packagist.org/packages/nekufa/di)
[![License](https://poser.pugx.org/nekufa/di/license.png)](https://packagist.org/packages/nekufa/di)
[![Build Status](https://travis-ci.org/nekufa/di.svg)](https://travis-ci.org/nekufa/di)
[![Coverage Status](https://coveralls.io/repos/nekufa/di/badge.png)](https://coveralls.io/r/nekufa/di)


This component implements dependency injection pattern.   
Manager can inject properties, configure objects and resolve depenencies while calling methods.  

# Installation
Usin composer.
```json
{
    "require": {
        "nekufa/di": "*"    
    }
}
```

# Object configuration
Configuration param is optional, but it is very useful for configure instances.

```php
<?php

use Nekufa\Di\Configuration;
use Nekufa\Di\Manager;

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

use Nekufa\Di\Configuration;

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

# Constructor injection
You can inject your dependencies in costructor
```php
<?php

use Nekufa\Di\Manager;

class Module
{

}

class Application
{
    protected $module;

    function __construct(Module $module)
    {
        $this->module = $module;
    }
}

$manager = new Manager();

// create Module, inject it in constructor and return application
$manager->get('Application');
```

# Object initialization

After your object were created, properties was set and all dependencies were injected init method is called.

```php
<?php

use Nekufa\Di\Manager;

class Module
{

}

class Application
{
    protected $module;
    public $property;

    function __construct(Module $module)
    {
        $this->module = $module;
    }

    function init()
    {
        echo '@value = ' . $this->property . PHP_EOL;
        echo '@property is instance of '. get_class($this->module);
    }
}

$manager = new Manager();

// create Module, inject it in constructor, call init and return application
$manager->get('Application', array(
    'property' => 'my_value'
));
```

# Property injection
One of usefull scenario is to inject dependencies when object is created.  
Injection works recursive, so if module requires another dependency - it would be resolved. 

```php
<?php

use Nekufa\Di\Configuration;
use Nekufa\Di\Manager;

class Application
{
    /**
     * @inject
     * @var  Module
     */
    protected $module;

    public function init()
    {
        echo "Application works with module.state = " . $this->module->state;
    }
}

class Module
{
    public $state;
}

$manager = new Manager();

// change class configuration
$manager->getConfiguration()->set('Module', 'state', 'active');


// create Module, set state property, create Application, inject module and call init
$manager->get('Application');

```

# Method caller
One of the killer feature is ability resolve dependencies for method calling.

```php
<?php

use Nekufa\Di\Manager;

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

# Using Alias
You can alias one class with another or use this technique to select interface implementation

```php
<?php

use Nekufa\Di\Manager;

interface GatewayInterface {}

class DirectGateway implements GatewayInterface {}
class ProxyGateway implements GatewayInterface {}

class Application
{
    /**
     * @inject
     * @var GatewayInterface
     */
    private $gateway;

    function init()
    {
        echo get_class($this->gateway);
    }
}

$manager = new Manager;
$manager->setAlias('DirectGateway', 'GatewayInterface');
$manager->get('Application'); // prints DirectGateway

$manager = new Manager;
$manager->setAlias('ProxyGateway', 'GatewayInterface');
$manager->get('Application'); // prints ProxyGateway

```