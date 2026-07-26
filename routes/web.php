<?php

declare(strict_types=1);

use App\Http\Controllers\Dashboard\RuntimeDashboardController;
use App\Http\Controllers\Dashboard\RuntimeDiagnosticsController;
use App\Http\Controllers\Dashboard\RuntimeGraphController;
use App\Http\Controllers\Dashboard\RuntimeModuleController;
use App\Http\Controllers\Dashboard\RuntimeRegistrationInspectorController;
use App\Http\Controllers\Dashboard\RuntimeRegistryController;
use App\Http\Controllers\Dashboard\RuntimeSearchController;
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
    '/control-centre/runtime/search',
    RuntimeSearchController::class,
)->name('control-centre.runtime.search');

Route::get(
    '/control-centre/runtime/registries/{registry}',
    RuntimeRegistryController::class,
)->name('control-centre.runtime.registries.show');

Route::get(
    '/control-centre/runtime/inspect/{registry}/{module}/{key}',
    RuntimeRegistrationInspectorController::class,
)->name('control-centre.runtime.inspector.show');

Route::get(
    '/control-centre/modules/{slug}',
    RuntimeModuleController::class,
)->name('control-centre.modules.show');

Route::get(
    '/control-centre/runtime/diagnostics',
    RuntimeDiagnosticsController::class,
)->name('control-centre.runtime.diagnostics');
