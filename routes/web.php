<?php

declare(strict_types=1);

use App\Modules\Branch\Presentation\Http\Controllers\BranchSelectionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home', [
    'appName' => config('app.name'),
]))->name('home');

// Storefront: choose the active branch; persisted in the session and exposed
// to every Inertia page via shared props. Throttled as light abuse protection.
Route::post('/branch/select', [BranchSelectionController::class, 'store'])
    ->middleware('throttle:60,1')
    ->name('branch.select');

require __DIR__.'/auth.php';
