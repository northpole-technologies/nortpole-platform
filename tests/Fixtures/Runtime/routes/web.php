<?php

use Illuminate\Support\Facades\Route;

Route::get(
    '/runtime-test/web',
    static fn (): array => [
        'route' => 'web',
    ]
)->name('runtime-test.web');