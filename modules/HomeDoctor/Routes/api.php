<?php

use Illuminate\Support\Facades\Route;
use Modules\HomeDoctor\Http\Controllers\HomeDoctorController;

Route::get('/home-doctor/status', [HomeDoctorController::class, 'status'])
    ->name('home-doctor.api.status');
