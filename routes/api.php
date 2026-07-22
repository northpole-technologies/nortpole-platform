<?php

use App\Http\Controllers\Api\ModuleRegistryController;
use App\Http\Controllers\Api\OrganisationController;
use App\Http\Controllers\Api\PluginController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| API v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Marketplace Modules
    |--------------------------------------------------------------------------
    */

    Route::get('/modules', [ModuleRegistryController::class, 'index']);

    Route::get(
        '/modules/{marketplaceModule}',
        [ModuleRegistryController::class, 'show']
    );

    Route::post(
        '/modules/{marketplaceModule}/install',
        [ModuleRegistryController::class, 'install']
    );

    Route::patch(
        '/modules/{marketplaceModule}/disable',
        [ModuleRegistryController::class, 'disable']
    );

    Route::patch(
        '/modules/{marketplaceModule}/enable',
        [ModuleRegistryController::class, 'enable']
    );

    /*
    |--------------------------------------------------------------------------
    | Organisations
    |--------------------------------------------------------------------------
    */

    Route::get('/organisations', [OrganisationController::class, 'index']);
    Route::post('/organisations', [OrganisationController::class, 'store']);

    Route::get(
        '/organisations/{organisation}',
        [OrganisationController::class, 'show']
    );

    Route::patch(
        '/organisations/{organisation}',
        [OrganisationController::class, 'update']
    );

    Route::delete(
        '/organisations/{organisation}',
        [OrganisationController::class, 'destroy']
    );
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('plugins', PluginController::class);
});