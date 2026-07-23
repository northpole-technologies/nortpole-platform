<?php

namespace Northpole\Core;

use Illuminate\Support\ServiceProvider;
use Northpole\Core\Runtime\ModuleRuntime;
use Northpole\Modules\ModuleManager;

class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleManager::class, function () {
            return new ModuleManager();
        });
    }

    public function boot(): void
    {
        /** @var ModuleManager $moduleManager */
        $moduleManager = $this->app->make(ModuleManager::class);

        /** @var ModuleRuntime $runtime */
        $runtime = $this->app->make(ModuleRuntime::class);

        $modules = $moduleManager->discover();

        $runtime->bootEnabledModules($modules);
    }
}