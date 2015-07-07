# Manage by config file

The easiest way is to use configuration file in php format.  
For example you have file `/path/to/config.php' wich is in your version control:

```php
<?php return array(
    'ClassName' => array(
        'property' => 'value',
        'anotherProperty' => 'value',
    ),

    'Interface' => 'InterfaceImplementation'
);
```

You can create another file '/path/to/local.config.php' and exclude it from version control.  
In this file you override some class properties:

```php
<?php return array(
    'ClassName' => array(
        'property' => 'new-value'
    )
);
```

Usage of two files is pretty simple - use load method and Configuration will lookup "local.*" file itself.

```php
<?php

use Cti\Di\Configuration;

$configuration = new Configuration;
$configuration->load('/path/to/config.php');

```

# Manage by hand

Configuration object is optional, but it is very useful for instances configuration.

```php
<?php

use Cti\Di\Configuration;
use Cti\Di\Manager;

$configuration = new Configuration(array(
    'ClassName' => array(
        'property' => 'value'
    )
));

$manager = new Manager($configuration);

// class property was set while creating instance
$manager->create('ClassName')->property; // value

```

You can merge configuration from different arrays and set properties directly.

```php
<?php

use Cti\Di\Configuration;

$configuration = new Configuration();

// override one property is easy
$configuration->set('Database', 'hostname', '192.168.2.87');

// override multiple properties
$configuration->merge(array(
    'Database' => array(
        'username' => 'Cti',
        'password' => 'secret',
        'hostname' => '192.168.2.91',
    )
));

// get full class configuration as array
$configuration->get('Database');

// or any property
$configuration->get('Database', 'username');

```
