<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TotpResetController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Register
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // TOTP Verification (after login)
    Route::get('/totp/verify', [AuthController::class, 'showTotpVerification'])->name('totp.verify');
    Route::post('/totp/verify', [AuthController::class, 'verifyTotp']);

    // TOTP Setup (after register)
    Route::get('/totp/setup', [AuthController::class, 'showTotpSetup'])->name('totp.setup');
    Route::post('/totp/setup', [AuthController::class, 'confirmTotpSetup']);

    // TOTP Reset
    Route::get('/totp/reset', [TotpResetController::class, 'showRequestForm'])->name('totp.reset.request');
    Route::post('/totp/reset', [TotpResetController::class, 'sendResetOtp']);
    Route::get('/totp/reset/verify', [TotpResetController::class, 'showVerifyOtpForm'])->name('totp.reset.verify');
    Route::post('/totp/reset/verify', [TotpResetController::class, 'verifyOtp']);
    Route::get('/totp/reset/confirm', [TotpResetController::class, 'showResetForm'])->name('totp.reset.confirm');
    Route::post('/totp/reset/confirm', [TotpResetController::class, 'confirmReset']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/', function () {
    return redirect()->route('login');
});
