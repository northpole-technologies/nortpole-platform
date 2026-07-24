<?php

use App\Http\Controllers\Api\ModuleRegistryController;
use App\Http\Controllers\Api\Organisation\PermissionController;
use App\Http\Controllers\Api\Organisation\RoleController;
use App\Http\Controllers\Api\OrganisationController;
use App\Http\Controllers\Api\PluginController;
use App\Http\Controllers\AuthController;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [
    AuthController::class,
    'register',
]);

Route::post('/login', [
    AuthController::class,
    'login',
]);

/*
|--------------------------------------------------------------------------
| API v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Marketplace Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/modules', [
        ModuleRegistryController::class,
        'index',
    ]);

    Route::get('/modules/{marketplaceModule}', [
        ModuleRegistryController::class,
        'show',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Tenant-Protected Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'auth:sanctum',
        'tenant',
    ])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Modules
        |--------------------------------------------------------------------------
        */

        Route::post('/modules/{marketplaceModule}/install', [
            ModuleRegistryController::class,
            'install',
        ]);

        Route::patch('/modules/{marketplaceModule}/disable', [
            ModuleRegistryController::class,
            'disable',
        ]);

        Route::patch('/modules/{marketplaceModule}/enable', [
            ModuleRegistryController::class,
            'enable',
        ]);

        Route::delete('/modules/{marketplaceModule}/uninstall', [
            ModuleRegistryController::class,
            'uninstall',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        Route::get('/roles', [
            RoleController::class,
            'index',
        ])->middleware('permission:roles.view');

        Route::post('/roles', [
            RoleController::class,
            'store',
        ])->middleware('permission:roles.create');

        Route::get('/roles/{role}', [
            RoleController::class,
            'show',
        ])->middleware('permission:roles.view');

        Route::patch('/roles/{role}', [
            RoleController::class,
            'update',
        ])->middleware('permission:roles.update');

        Route::post('/roles/{role}/permissions', [
            RoleController::class,
            'syncPermissions',
        ])->middleware('permission:roles.permissions.sync');

        Route::delete('/roles/{role}', [
            RoleController::class,
            'destroy',
        ])->middleware('permission:roles.delete');

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        Route::get('/permissions', [
            PermissionController::class,
            'index',
        ])->middleware('permission:permissions.view');

        /*
        |--------------------------------------------------------------------------
        | Tenant Context
        |--------------------------------------------------------------------------
        */

        Route::get('/tenant/context', function (
            TenantContext $tenantContext
        ) {
            return response()->json([
                'success' => true,
                'data' => [
                    'organisation' => $tenantContext->organisation(),
                ],
            ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Organisations
    |--------------------------------------------------------------------------
    */

    Route::get('/organisations', [
        OrganisationController::class,
        'index',
    ]);

    Route::post('/organisations', [
        OrganisationController::class,
        'store',
    ]);

    Route::get('/organisations/{organisation}', [
        OrganisationController::class,
        'show',
    ]);

    Route::patch('/organisations/{organisation}', [
        OrganisationController::class,
        'update',
    ]);

    Route::delete('/organisations/{organisation}', [
        OrganisationController::class,
        'destroy',
    ]);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [
        AuthController::class,
        'user',
    ]);

    Route::post('/logout', [
        AuthController::class,
        'logout',
    ]);

    Route::apiResource(
        'plugins',
        PluginController::class
    );
});