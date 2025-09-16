<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PemilikTokoController;

// Route utama OSS - tampilkan halaman produk berdasarkan login status
Route::get('/', [HomeController::class, 'index'])->name('home');

// Route khusus untuk customer area (untuk link "Kembali ke Area Customer")
Route::get('/customer-area', function() {
    if (!auth()->check()) {
        return redirect()->route('login')->with('error', 'Silakan login untuk mengakses area customer.');
    }
    
    $user = auth()->user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard')->with('error', 'Admin tidak dapat mengakses area customer.');
    }
    
    // Untuk customer dan pemilik_toko, tampilkan halaman customer
    $kategoris = \App\Models\Kategori::all();
    return view('halaman-produk-customer', compact('kategoris'));
})->name('customer.area');

// Route untuk view detail produk via AJAX - untuk semua user (guest & customer)
Route::get('/produk/view/{id}', [HomeController::class, 'viewProduk'])->name('produk.view');

// Route untuk filter kategori - untuk semua user
Route::get('/kategori/{id}', [HomeController::class, 'viewKategoriProduk'])->name('kategori.view');

// Route untuk feedback visitor/guest tanpa login
Route::post('/guest-feedback', [HomeController::class, 'submitGuestFeedback'])->name('guest.feedback');

// Route dashboard default Breeze - PERBAIKAN LOGIC REDIRECT
Route::get('/dashboard', function () {
    // Redirect berdasarkan role user
    if (auth()->check()) {
        $user = auth()->user();
        
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'customer') {
            return redirect()->route('customer.area');
        } elseif ($user->role === 'pemilik_toko') {
            // PENTING: Pemilik toko juga masuk ke customer area, BUKAN dashboard toko
            return redirect()->route('customer.area');
        }
    }
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard admin
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Manage Customers
    Route::get('/customers', [AdminController::class, 'manageCustomers'])->name('customers');
    Route::get('/customers/{id}/edit', [AdminController::class, 'editCustomer'])->name('customers.edit');
    Route::patch('/customers/{id}', [AdminController::class, 'updateCustomer'])->name('customers.update');
    Route::delete('/customers/{id}', [AdminController::class, 'deleteCustomer'])->name('customers.delete');
    
    // Manage Kategori
    Route::get('/kategori', [AdminController::class, 'manageKategori'])->name('kategori');
    Route::post('/kategori/store', [AdminController::class, 'storeKategori'])->name('kategori.store');
    Route::get('/kategori/{id}/edit', [AdminController::class, 'editKategori'])->name('kategori.edit');
    Route::patch('/kategori/{id}', [AdminController::class, 'updateKategori'])->name('kategori.update');
    Route::delete('/kategori/{id}', [AdminController::class, 'deleteKategori'])->name('kategori.delete');
    
    // Manage Toko Requests dengan DELETE
    Route::get('/toko/requests', [AdminController::class, 'manageTokoRequests'])->name('toko.requests');
    Route::get('/toko/requests/{id}/detail', [AdminController::class, 'viewTokoRequestDetail'])->name('toko.detail');
    Route::post('/toko/approve/{id}', [AdminController::class, 'approveTokoRequest'])->name('toko.approve');
    Route::post('/toko/reject/{id}', [AdminController::class, 'rejectTokoRequest'])->name('toko.reject');
    Route::delete('/toko/delete/{id}', [AdminController::class, 'deleteTokoRequest'])->name('toko.delete');
    
    // Manage Guest Book
    Route::get('/guestbook', [AdminController::class, 'manageGuestBook'])->name('guestbook');
    Route::post('/guestbook/approve/{id}', [AdminController::class, 'approveFeedback'])->name('guestbook.approve');
    Route::post('/guestbook/reject/{id}', [AdminController::class, 'rejectFeedback'])->name('guestbook.reject');
    Route::delete('/guestbook/{id}', [AdminController::class, 'deleteFeedback'])->name('guestbook.delete');
    
    // Manage Shipping Orders
    Route::get('/shipping', [AdminController::class, 'manageShippingOrders'])->name('shipping');
    Route::get('/shipping/create/{transaksi_id}', [AdminController::class, 'createShippingOrder'])->name('shipping.create');
    Route::post('/shipping/store', [AdminController::class, 'storeShippingOrder'])->name('shipping.store');
    Route::patch('/shipping/{id}/status', [AdminController::class, 'updateShippingStatus'])->name('shipping.status');
});

// Customer Routes - hanya untuk role customer
Route::middleware(['auth', 'role:customer'])->group(function () {
    // Buy dari kategori
    Route::post('/buy-from-kategori/{kategori_id}', [CustomerController::class, 'buyFromKategori'])->name('customer.buy.from.kategori');
    
    // Keranjang management
    Route::get('/keranjang', [CustomerController::class, 'keranjang'])->name('customer.keranjang');
    Route::patch('/keranjang/update/{keranjang_id}', [CustomerController::class, 'updateKeranjang'])->name('customer.keranjang.update');
    Route::delete('/keranjang/hapus/{keranjang_id}', [CustomerController::class, 'hapusKeranjang'])->name('customer.keranjang.hapus');
    
    // Checkout
    Route::post('/checkout', [CustomerController::class, 'checkout'])->name('customer.checkout');
    
    // Order History
    Route::get('/order-history', [CustomerController::class, 'orderHistory'])->name('customer.order.history');
    Route::post('/cancel-order/{id}', [CustomerController::class, 'cancelOrder'])->name('customer.cancel.order');
    Route::get('/download-laporan/{transaksi_id}', [CustomerController::class, 'downloadLaporan'])->name('customer.download.laporan');
    
    // Feedback customer
    Route::post('/feedback', [CustomerController::class, 'submitFeedback'])->name('customer.feedback');
    
    // Permohonan Toko
    Route::get('/toko/request', [CustomerController::class, 'showTokoRequestForm'])->name('customer.toko.request');
    Route::post('/toko/request', [CustomerController::class, 'submitTokoRequest'])->name('customer.toko.submit');
    Route::get('/toko/status', [CustomerController::class, 'viewTokoStatus'])->name('customer.toko.status');
    
    // AJAX Routes
    Route::get('/keranjang/data', [CustomerController::class, 'getKeranjangData'])->name('customer.keranjang.data');
});

// Pemilik Toko Routes - menggunakan controller khusus dan customer controller
Route::middleware(['auth', 'role:pemilik_toko'])->prefix('pemilik-toko')->name('pemilik-toko.')->group(function () {
    // Dashboard pemilik toko
    Route::get('/dashboard', function() {
        return view('pemilik-toko.dashboard');
    })->name('dashboard');
    
    // Kelola kategori dengan controller khusus (read-only)
    Route::get('/kategori', [PemilikTokoController::class, 'manageKategori'])->name('kategori');
    Route::post('/kategori/store', [PemilikTokoController::class, 'storeKategori'])->name('kategori.store');
    Route::get('/kategori/{id}/edit', [PemilikTokoController::class, 'editKategori'])->name('kategori.edit');
    Route::patch('/kategori/{id}', [PemilikTokoController::class, 'updateKategori'])->name('kategori.update');
    Route::delete('/kategori/{id}', [PemilikTokoController::class, 'deleteKategori'])->name('kategori.delete');
    
    // Kelola shipping orders dengan controller khusus
    Route::get('/shipping', [PemilikTokoController::class, 'manageShippingOrders'])->name('shipping');
    
    // Pemilik toko bisa akses semua fitur customer dengan prefix
    // Buy dari kategori (sama seperti customer)
    Route::post('/buy-from-kategori/{kategori_id}', [CustomerController::class, 'buyFromKategori'])->name('buy.from.kategori');
    
    // Keranjang management (sama seperti customer)
    Route::get('/keranjang', [CustomerController::class, 'keranjang'])->name('keranjang');
    Route::patch('/keranjang/update/{keranjang_id}', [CustomerController::class, 'updateKeranjang'])->name('keranjang.update');
    Route::delete('/keranjang/hapus/{keranjang_id}', [CustomerController::class, 'hapusKeranjang'])->name('keranjang.hapus');
    
    // Checkout (sama seperti customer)
    Route::post('/checkout', [CustomerController::class, 'checkout'])->name('checkout');
    
    // Order History (sama seperti customer)
    Route::get('/order-history', [CustomerController::class, 'orderHistory'])->name('order.history');
    Route::post('/cancel-order/{id}', [CustomerController::class, 'cancelOrder'])->name('cancel.order');
    Route::get('/download-laporan/{transaksi_id}', [CustomerController::class, 'downloadLaporan'])->name('download.laporan');
    
    // Feedback (sama seperti customer)
    Route::post('/feedback', [CustomerController::class, 'submitFeedback'])->name('feedback');
    
    // AJAX Routes untuk pemilik toko
    Route::get('/keranjang/data', [CustomerController::class, 'getKeranjangData'])->name('keranjang.data');
});

// Public Routes untuk visitor
Route::get('/kategori/view/{id}', function($id) {
    return redirect()->route('home', ['kategori_id' => $id]);
})->name('kategori.view.guest');

// Authentication Routes
require __DIR__.'/auth.php';

// Fallback Route
Route::fallback(function () {
    return redirect()->route('home');
});