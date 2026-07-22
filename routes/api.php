<?php

use App\Http\Controllers\Api\OrganisationController;
use App\Http\Controllers\Api\PluginController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::prefix('v1')->group(function () {
    Route::post('/organisations', [OrganisationController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('plugins', PluginController::class);
});