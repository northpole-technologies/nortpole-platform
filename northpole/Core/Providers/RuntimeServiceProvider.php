<?php

namespace Northpole\Core\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Northpole\Core\Runtime\ModuleRuntime;

class RuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ModuleRuntime::class,
            function (Application $app): ModuleRuntime {
                return new ModuleRuntime($app);
            }
        );
    }

    public function boot(): void
    {
        //
    }
}