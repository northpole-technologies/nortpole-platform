<?php

namespace Modules\SantaBuddy\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SantaBuddyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../../routes/web.php');

        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/../../routes/api.php');

        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'santabuddy'
        );

        $this->loadMigrationsFrom(
            __DIR__ . '/../../database/migrations'
        );

        $this->loadTranslationsFrom(
            __DIR__ . '/../../resources/lang',
            'santabuddy'
        );
    }
}