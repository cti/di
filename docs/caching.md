# Dump cache
If you use di you can see, that reflection is not so fast.  
Of course, you need to generate dependencies first.
```php
<?php

// warm cache
$manager = new Manager;
$manager->get('Acme\Processor');

$filename = 'cache/di.php';
$data = $manager->getCache()->getData();

file_put_contents($filename, '<?php return ' . var_export($data, true) . ';');
```

# Load cache

All reflection operations are stored in cache, you can dump and load it when application starts.

```php
<?php

$cached = include 'cache/di.php';

$manager = new Manager();
$manager->getCache()->setData($cached);

// now all dependencies would be not discovered, because di known about them
$processor = $manager->get('Acme\Processor');

```

