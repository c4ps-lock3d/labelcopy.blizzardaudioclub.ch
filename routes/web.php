<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReleaseController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Release;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'releases' => Release::with('release_type')->get()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard/add-release', function () {
    return Inertia::render('AddRelease', [
        'releases' => Release::with('release_type')->get()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard.addrelease');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('store-release', [ReleaseController::class, 'store'])->name('store-release');
});

require __DIR__.'/auth.php';
