<?php

use Northpole\Loader\ModuleLoader;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$loader = new ModuleLoader;

print_r($loader->discover());
