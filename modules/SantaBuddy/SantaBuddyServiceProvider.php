<?php

namespace Modules\SantaBuddy;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class SantaBuddyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Log::info('SantaBuddy module loaded successfully.');
    }
}
