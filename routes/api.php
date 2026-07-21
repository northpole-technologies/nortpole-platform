<?php

use App\Http\Controllers\OrganisationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/organisations', [OrganisationController::class, 'store']);
});