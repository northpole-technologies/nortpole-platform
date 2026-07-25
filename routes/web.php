<?php

declare(strict_types=1);

use App\Http\Controllers\Dashboard\RuntimeDashboardController;
use App\Http\Controllers\Dashboard\RuntimeGraphController;
use App\Http\Controllers\Dashboard\RuntimeModuleController;
use App\Http\Controllers\Dashboard\RuntimeDiagnosticsController;
use App\Http\Controllers\Dashboard\RuntimeRegistryController;
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
    '/control-centre/runtime/graph',
    RuntimeGraphController::class,
)->name('control-centre.runtime.graph');

Route::get(
    '/control-centre/runtime/registries/{registry}',
    RuntimeRegistryController::class,
)->name('control-centre.runtime.registries.show');

Route::get(
    '/control-centre/modules/{slug}',
    RuntimeModuleController::class,
)->name('control-centre.modules.show');
Route::get(
    '/control-centre/runtime/diagnostics',
    RuntimeDiagnosticsController::class,
)->name('control-centre.runtime.diagnostics');
