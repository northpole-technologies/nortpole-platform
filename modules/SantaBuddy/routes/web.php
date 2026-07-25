<?php

use Illuminate\Support\Facades\Route;
use Modules\SantaBuddy\Http\Controllers\Web\HomeController;

Route::get('/santa-buddy', [HomeController::class, 'index'])
    ->name('santa-buddy.home');
