<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomerController;

// Route utama OSS custom (tampil halaman produk seperti screenshot)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Route dashboard default Breeze (fix "dashboard not defined")
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Routes Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/customers', [AdminController::class, 'manageCustomers'])->name('customers');
    Route::get('/kategori', [AdminController::class, 'manageKategori'])->name('kategori');
    Route::post('/kategori/store', [AdminController::class, 'storeKategori'])->name('kategori.store');
    Route::get('/toko/requests', [AdminController::class, 'manageTokoRequests'])->name('toko.requests');
    Route::post('/toko/approve/{id}', [AdminController::class, 'approveToko'])->name('toko.approve');
    Route::post('/toko/reject/{id}', [AdminController::class, 'rejectToko'])->name('toko.reject');
});

// Routes Customer
Route::middleware(['auth', 'role:customer'])->name('customer.')->group(function () {
    Route::post('/keranjang/tambah/{produk_id}', [CustomerController::class, 'tambahKeKeranjang'])->name('keranjang.tambah');
    Route::get('/keranjang', [CustomerController::class, 'keranjang'])->name('keranjang');
    Route::post('/checkout', [CustomerController::class, 'checkout'])->name('checkout');
});

// Import Breeze routes (login/register/profile)
require __DIR__.'/auth.php';