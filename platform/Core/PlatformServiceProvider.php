<?php

namespace Platform\Core;

use Illuminate\Support\ServiceProvider;
use Platform\Modules\ModuleManager;
use Platform\Registry\ModuleRegistry;

class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class, function () {
            return new ModuleRegistry();
        });

        $this->app->singleton(Platform::class, function () {
            return new Platform();
        });

        $this->app->singleton(ModuleManager::class, function () {
            return new ModuleManager();
        });
    }

    public function boot(): void
    {
        logger()->info('PlatformServiceProvider booted');

        $manager = $this->app->make(ModuleManager::class);

        logger()->info('ModuleManager resolved');

        $modules = $manager->discover();

        logger()->info('Modules discovered: ' . count($modules));

        foreach ($modules as $module) {
            logger()->info('Module: ' . json_encode($module));
        }
    }
}