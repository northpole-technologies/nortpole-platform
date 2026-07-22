<?php

namespace Northpole\Core;

use Illuminate\Support\ServiceProvider;
use Northpole\Modules\ModuleManager;

class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleManager::class, function () {
            return new ModuleManager();
        });

        $moduleManager = $this->app->make(ModuleManager::class);

        $moduleManager->discover();

        foreach ($moduleManager->providers() as $provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

    public function boot(): void
    {
        //
    }
}