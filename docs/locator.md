# Service locator implementation

Locator is on the top of di package.  
Of course you can use load method (as in configuration) for loading from file.
Usage example:

```php
<?php

use Cti\Di\Locator;

$config = array(

    // define only class
    'service1' => 'ServiceClass',

    // define full configuration
    'service2' => array(
        'class' => 'ServiceClass',
        'config' => array(
            'property' => 'value',
        )
    )

    // short syntax suport
    'service3' => array(
        'ServiceClass',
        'property' => 'value', 
        'another_property' => 'value'
    ),

    // service reference
    'service4' => array(
        'ServiceClass',
        'another_service_reference' => '@service3'
    ),
);

$locator = new Locator();
$locator->load($config);

// register in runtime
$locator->register('service5', 'AnotherService');

// register with magic method
$locator->service6 = 'MyClass';

```

# Service usage
After configure locator you can access services.

```php
<?php

// instance of ServiceClass
$locator->get('service1');

// use magic getter via callback defined service 3
$locator->getService2();

// use virtual property;
$locator->service3;

```