<?php

declare(strict_types=1);

use App\Http\Controllers\Dashboard\RuntimeDashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect(
    '/',
    '/control-centre',
);

Route::get(
    '/control-centre',
    RuntimeDashboardController::class,
)->name('control-centre');
