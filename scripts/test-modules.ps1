<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$loader = new Northpole\Loader\ModuleLoader();

print_r($loader->discover());