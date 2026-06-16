<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home', [
    'appName' => config('app.name'),
]))->name('home');

require __DIR__.'/auth.php';
