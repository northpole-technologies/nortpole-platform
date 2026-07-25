<?php

declare(strict_types=1);

use App\Http\Controllers\Dashboard\RuntimeDashboardController;
use App\Http\Controllers\Dashboard\RuntimeModuleController;
use Illuminate\Support\Facades\Route;

Route::redirect(
    '/',
    '/control-centre',
);

Route::get(
    '/control-centre',
    RuntimeDashboardController::class,
)->name('control-centre');

Route::get(
    '/control-centre/modules/{slug}',
    RuntimeModuleController::class,
)->name('control-centre.modules.show');
