<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomerController;

// Route utama OSS - tampilkan halaman produk untuk visitor/customer
Route::get('/', [HomeController::class, 'index'])->name('home');

// Route untuk view detail produk via AJAX - untuk semua user
Route::get('/produk/view/{id}', [HomeController::class, 'viewProduk'])->name('produk.view');

// Route untuk filter kategori - untuk semua user
Route::get('/kategori/{id}', [HomeController::class, 'index'])->name('kategori.filter');

// Route dashboard default Breeze - untuk user yang sudah login
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Routes Admin - menggunakan middleware auth dan role admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard admin
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Manage Customers - CRUD customer
    Route::get('/customers', [AdminController::class, 'manageCustomers'])->name('customers');
    Route::get('/customers/{id}/edit', [AdminController::class, 'editCustomer'])->name('customers.edit');
    Route::patch('/customers/{id}', [AdminController::class, 'updateCustomer'])->name('customers.update');
    Route::delete('/customers/{id}', [AdminController::class, 'deleteCustomer'])->name('customers.delete');
    
    // Manage Kategori - CRUD kategori dengan category type
    Route::get('/kategori', [AdminController::class, 'manageKategori'])->name('kategori');
    Route::post('/kategori/store', [AdminController::class, 'storeKategori'])->name('kategori.store');
    Route::get('/kategori/{id}/edit', [AdminController::class, 'editKategori'])->name('kategori.edit');
    Route::patch('/kategori/{id}', [AdminController::class, 'updateKategori'])->name('kategori.update');
    Route::delete('/kategori/{id}', [AdminController::class, 'deleteKategori'])->name('kategori.delete');
    
    // Manage Toko Requests - approve/reject toko
    Route::get('/toko/requests', [AdminController::class, 'manageTokoRequests'])->name('toko.requests');
    Route::post('/toko/approve/{id}', [AdminController::class, 'approveToko'])->name('toko.approve');
    Route::post('/toko/reject/{id}', [AdminController::class, 'rejectToko'])->name('toko.reject');
    
    // Manage Guest Book - moderasi feedback customer
    Route::get('/guestbook', [AdminController::class, 'manageGuestBook'])->name('guestbook');
    Route::post('/guestbook/approve/{id}', [AdminController::class, 'approveFeedback'])->name('guestbook.approve');
    Route::post('/guestbook/reject/{id}', [AdminController::class, 'rejectFeedback'])->name('guestbook.reject');
    Route::delete('/guestbook/{id}', [AdminController::class, 'deleteFeedback'])->name('guestbook.delete');
    
    // Manage Shipping Orders - kelola pengiriman
    Route::get('/shipping', [AdminController::class, 'manageShippingOrders'])->name('shipping');
    Route::get('/shipping/create/{transaksi_id}', [AdminController::class, 'createShippingOrder'])->name('shipping.create');
    Route::post('/shipping/store', [AdminController::class, 'storeShippingOrder'])->name('shipping.store');
    Route::patch('/shipping/{id}/status', [AdminController::class, 'updateShippingStatus'])->name('shipping.status');
});

// Routes Customer - menggunakan middleware auth dan role customer
Route::middleware(['auth', 'role:customer'])->name('customer.')->group(function () {
    // Keranjang belanja
    Route::post('/keranjang/tambah/{produk_id}', [CustomerController::class, 'tambahKeKeranjang'])->name('keranjang.tambah');
    Route::get('/keranjang', [CustomerController::class, 'keranjang'])->name('keranjang');
    Route::patch('/keranjang/update/{keranjang_id}', [CustomerController::class, 'updateKeranjang'])->name('keranjang.update');
    Route::delete('/keranjang/hapus/{keranjang_id}', [CustomerController::class, 'hapusKeranjang'])->name('keranjang.hapus');
    
    // Checkout dan transaksi
    Route::post('/checkout', [CustomerController::class, 'checkout'])->name('checkout');
    Route::post('/cancel-order/{id}', [CustomerController::class, 'cancelOrder'])->name('cancel.order');
    Route::get('/download-laporan/{transaksi_id}', [CustomerController::class, 'downloadLaporan'])->name('download.laporan');
    
    // Feedback dan account
    Route::post('/feedback', [CustomerController::class, 'submitFeedback'])->name('feedback');
    Route::get('/account', [CustomerController::class, 'viewAccount'])->name('account');
});

// Import Breeze routes - untuk login/register/profile
require __DIR__.'/auth.php';