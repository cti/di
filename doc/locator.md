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
```