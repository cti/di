# Constructor injection
You can inject your dependencies in costructor
```php
<?php

use Cti\Di\Manager;

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

use Cti\Di\Manager;

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

use Cti\Di\Configuration;
use Cti\Di\Manager;

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

use Cti\Di\Manager;

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

use Cti\Di\Manager;

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