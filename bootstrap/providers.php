<?php

use App\Providers\AppServiceProvider;
use Northpole\Core\PlatformServiceProvider;

return [
    AppServiceProvider::class,
    PlatformServiceProvider::class,
    Northpole\Runtime\Providers\NorthpoleRuntimeServiceProvider::class,
];
