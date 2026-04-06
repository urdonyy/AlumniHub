<?php

use App\Http\Controllers\Admin\VerificationAdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/verification', [VerificationController::class, 'store'])->name('verification.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/verifications', [VerificationAdminController::class, 'index'])->name('verifications.index');
    Route::get('/verifications/{verificationDocument}/document', [VerificationAdminController::class, 'viewDocument'])->name('verifications.document');
    Route::patch('/verifications/{verificationDocument}/approve', [VerificationAdminController::class, 'approve'])->name('verifications.approve');
    Route::patch('/verifications/{verificationDocument}/reject', [VerificationAdminController::class, 'reject'])->name('verifications.reject');
});

require __DIR__ . '/auth.php';
