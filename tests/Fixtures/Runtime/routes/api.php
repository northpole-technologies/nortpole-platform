<?php

use Illuminate\Support\Facades\Route;

Route::get(
    '/runtime-test/api',
    static fn (): array => [
        'route' => 'api',
    ]
)->name('runtime-test.api');