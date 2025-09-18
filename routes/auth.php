<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController; 
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController; 
use App\Http\Controllers\Auth\PasswordResetLinkController; 
use App\Http\Controllers\Auth\RegisteredUserController; 
use App\Http\Controllers\Auth\VerifyEmailController; 
use App\Http\Controllers\ProfileController; 
use Illuminate\Support\Facades\Route;

// Rute untuk halaman registrasi
Route::get('/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest') // Hanya akses oleh pengguna tamu
    ->name('register'); // Nama rute: register

// Rute untuk menyimpan data registrasi
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest'); // Hanya akses oleh pengguna tamu

// Rute untuk halaman login
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest') // Hanya akses oleh pengguna tamu
    ->name('login'); // Nama rute: login

// Rute untuk menyimpan data login
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest'); // Hanya akses oleh pengguna tamu

// Rute untuk halaman lupa kata sandi
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest') // Hanya akses oleh pengguna tamu
    ->name('password.request'); // Nama rute: password.request

// Rute untuk mengirim tautan reset kata sandi
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest') // Hanya akses oleh pengguna tamu
    ->name('password.email'); // Nama rute: password.email

// Rute untuk halaman reset kata sandi dengan token
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest') // Hanya akses oleh pengguna tamu
    ->name('password.reset'); // Nama rute: password.reset

// Rute untuk menyimpan kata sandi baru
Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest') // Hanya akses oleh pengguna tamu
    ->name('password.update'); // Nama rute: password.update

// Rute untuk halaman verifikasi email
Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware('auth') // Hanya akses oleh pengguna terautentikasi
    ->name('verification.notice'); // Nama rute: verification.notice

// Rute untuk memverifikasi email dengan ID dan hash
Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1']) // Membutuhkan autentikasi, tanda tangan, dan batas percobaan
    ->name('verification.verify'); // Nama rute: verification.verify

// Rute untuk mengirim ulang notifikasi verifikasi email
Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1']) // Membutuhkan autentikasi dan batas percobaan
    ->name('verification.send'); // Nama rute: verification.send

// Rute untuk halaman konfirmasi kata sandi
Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
    ->middleware('auth') // Hanya akses oleh pengguna terautentikasi
    ->name('password.confirm'); // Nama rute: password.confirm

// Rute untuk menyimpan konfirmasi kata sandi
Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])
    ->middleware('auth'); // Hanya akses oleh pengguna terautentikasi

// Rute untuk logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth') // Hanya akses oleh pengguna terautentikasi
    ->name('logout'); // Nama rute: logout

// Rute untuk mengedit profil
Route::get('/profile', [ProfileController::class, 'edit'])
    ->middleware('auth') // Hanya akses oleh pengguna terautentikasi
    ->name('profile.edit'); // Nama rute: profile.edit

// Rute untuk memperbarui profil
Route::patch('/profile', [ProfileController::class, 'update'])
    ->middleware('auth') // Hanya akses oleh pengguna terautentikasi
    ->name('profile.update'); // Nama rute: profile.update

// Rute untuk menghapus profil
Route::delete('/profile', [ProfileController::class, 'destroy'])
    ->middleware('auth') // Hanya akses oleh pengguna terautentikasi
    ->name('profile.destroy'); // Nama rute: profile.destroy