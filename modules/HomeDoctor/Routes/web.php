<?php

use Illuminate\Support\Facades\Route;
use Modules\HomeDoctor\Http\Controllers\HomeDoctorController;

Route::get('/home-doctor', [HomeDoctorController::class, 'index'])
    ->name('home-doctor.home');