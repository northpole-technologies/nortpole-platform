<?php

use Illuminate\Support\Facades\Route;
use Modules\SantaBuddy\Http\Controllers\Api\StatusController;

Route::get('/santa-buddy/status', [StatusController::class, 'index'])
    ->name('santa-buddy.api.status');
