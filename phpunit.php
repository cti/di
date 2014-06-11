<?php

$loader = include __DIR__.'/vendor/autoload.php';
$loader->add("Common", __DIR__.'/tests/Common');
$loader->add("Inject", __DIR__.'/tests/Inject');