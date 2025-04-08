<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReleaseController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Release;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return redirect('/login');
})->middleware('guest');

Route::get('/queue', function () {
    $exitCode = Artisan::call('queue:work --stop-when-empty');
})->middleware('auth.basic');

// Dashboard
Route::get('/dashboard', [ReleaseController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::put('/releases/{release}/status', [ReleaseController::class, 'updateStatus'])->name('update-release-status');
// Add
Route::get('/dashboard/add-release', [ReleaseController::class, 'add'])->middleware(['auth', 'verified'])->name('dashboard.addrelease');
Route::post('/dashboard/store-release', [ReleaseController::class, 'store'])->middleware(['auth', 'verified'])->name('store-release');
Route::post('/check-email', [ReleaseController::class, 'checkEmail'])->name('check-email');
// Edit
Route::get('/dashboard/{release}/edit-release', [ReleaseController::class, 'edit'])->middleware(['auth', 'verified'])->name('dashboard.editrelease');
Route::put('/dashboard/{release}/update-release', [ReleaseController::class, 'update'])->middleware(['auth', 'verified'])->name('update-release');

// Profil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
