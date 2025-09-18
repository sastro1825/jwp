<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PemilikTokoController;

// Rute utama untuk halaman produk berdasarkan status login
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rute untuk area pelanggan dengan data kategori toko
Route::get('/customer-area', function() {
    // Periksa status autentikasi pengguna
    if (!auth()->check()) {
        return redirect()->route('login')->with('error', 'Silakan login untuk mengakses area customer.');
    }
    
    // Ambil data pengguna yang sedang login
    $user = auth()->user();
    // Cek jika pengguna adalah admin
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard')->with('error', 'Admin tidak dapat mengakses area customer.');
    }
    
    // Ambil semua data kategori
    $kategoris = \App\Models\Kategori::all();
    
    // Ambil kategori toko dengan status toko yang disetujui
    $kategoriToko = \App\Models\TokoKategori::with('toko')
        ->whereHas('toko', function($query) {
            // Filter toko dengan status approved
            $query->where('status', 'approved');
        })
        ->get();

    // Tampilkan view halaman produk customer
    return view('halaman-produk-customer', compact('kategoris', 'kategoriToko'));
})->name('customer.area');

// Rute untuk menampilkan detail produk via AJAX untuk semua pengguna
Route::get('/produk/view/{id}', [HomeController::class, 'viewProduk'])->name('produk.view');

// Rute untuk filter produk berdasarkan kategori
Route::get('/kategori/{id}', [HomeController::class, 'viewKategoriProduk'])->name('kategori.view');

// Rute untuk submit feedback dari pengunjung tanpa login
Route::post('/guest-feedback', [HomeController::class, 'submitGuestFeedback'])->name('guest.feedback');

// Rute untuk dashboard default dengan redirect berdasarkan peran
Route::get('/dashboard', function () {
    // Cek status autentikasi dan arahkan berdasarkan peran
    if (auth()->check()) {
        $user = auth()->user();
        
        // Redirect admin ke dashboard admin
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        // Redirect customer ke area pelanggan
        } elseif ($user->role === 'customer') {
            return redirect()->route('customer.area');
        // Redirect pemilik toko ke area pelanggan
        } elseif ($user->role === 'pemilik_toko') {
            return redirect()->route('customer.area');
        }
    }
    // Tampilkan dashboard default jika tidak login
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Rute untuk admin dengan middleware autentikasi dan peran
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Rute dashboard admin
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Rute untuk mengelola pelanggan
    Route::get('/customers', [AdminController::class, 'manageCustomers'])->name('customers');
    Route::get('/customers/{id}/edit', [AdminController::class, 'editCustomer'])->name('customers.edit');
    Route::patch('/customers/{id}', [AdminController::class, 'updateCustomer'])->name('customers.update');
    Route::delete('/customers/{id}', [AdminController::class, 'deleteCustomer'])->name('customers.delete');
    
    // Rute baru untuk reset password pelanggan
    Route::patch('/customers/{id}/reset-password', [AdminController::class, 'resetCustomerPassword'])->name('customers.reset-password');
    
    // Rute untuk mengelola kategori
    Route::get('/kategori', [AdminController::class, 'manageKategori'])->name('kategori');
    Route::post('/kategori/store', [AdminController::class, 'storeKategori'])->name('kategori.store');
    Route::get('/kategori/{id}/edit', [AdminController::class, 'editKategori'])->name('kategori.edit');
    Route::patch('/kategori/{id}', [AdminController::class, 'updateKategori'])->name('kategori.update');
    Route::delete('/kategori/{id}', [AdminController::class, 'deleteKategori'])->name('kategori.delete');
    
    // Rute untuk mengelola permintaan toko
    Route::get('/toko/requests', [AdminController::class, 'manageTokoRequests'])->name('toko.requests');
    Route::get('/toko/requests/{id}/detail', [AdminController::class, 'viewTokoRequestDetail'])->name('toko.detail');
    Route::post('/toko/approve/{id}', [AdminController::class, 'approveTokoRequest'])->name('toko.approve');
    Route::post('/toko/reject/{id}', [AdminController::class, 'rejectTokoRequest'])->name('toko.reject');
    Route::delete('/toko/delete/{id}', [AdminController::class, 'deleteTokoRequest'])->name('toko.delete');
    
    // Rute untuk mengelola buku tamu
    Route::get('/guestbook', [AdminController::class, 'manageGuestBook'])->name('guestbook');
    Route::post('/guestbook/approve/{id}', [AdminController::class, 'approveFeedback'])->name('guestbook.approve');
    Route::post('/guestbook/reject/{id}', [AdminController::class, 'rejectFeedback'])->name('guestbook.reject');
    Route::delete('/guestbook/{id}', [AdminController::class, 'deleteFeedback'])->name('guestbook.delete');
    
    // Rute untuk mengelola pesanan pengiriman
    Route::get('/shipping', [AdminController::class, 'manageShippingOrders'])->name('shipping');
    Route::get('/shipping/create/{transaksi_id}', [AdminController::class, 'createShippingOrder'])->name('shipping.create');
    Route::post('/shipping/store', [AdminController::class, 'storeShippingOrder'])->name('shipping.store');
    Route::patch('/shipping/{id}/status', [AdminController::class, 'updateShippingStatus'])->name('shipping.status');
});

// Rute untuk pelanggan dengan middleware autentikasi dan peran
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::post('/buy-from-kategori/{kategori_id}', [CustomerController::class, 'buyFromKategori'])->name('customer.buy.from.kategori');
    Route::post('/buy-from-toko-kategori/{kategori_id}', [CustomerController::class, 'buyFromTokoKategori'])->name('customer.buy.from.toko.kategori');
    Route::get('/keranjang', [CustomerController::class, 'keranjang'])->name('customer.keranjang');
    Route::patch('/keranjang/update/{keranjang_id}', [CustomerController::class, 'updateKeranjang'])->name('customer.keranjang.update');
    Route::delete('/keranjang/hapus/{keranjang_id}', [CustomerController::class, 'hapusKeranjang'])->name('customer.keranjang.hapus');
    Route::post('/checkout', [CustomerController::class, 'checkout'])->name('customer.checkout');
    Route::get('/order-history', [CustomerController::class, 'orderHistory'])->name('customer.order.history');
    Route::post('/cancel-order/{id}', [CustomerController::class, 'cancelOrder'])->name('customer.cancel.order');
    Route::get('/download-laporan/{transaksi_id}', [CustomerController::class, 'downloadLaporan'])->name('customer.download.laporan');
    Route::post('/feedback', [CustomerController::class, 'submitFeedback'])->name('customer.feedback');
    Route::get('/toko/request', [CustomerController::class, 'showTokoRequestForm'])->name('customer.toko.request');
    Route::post('/toko/request', [CustomerController::class, 'submitTokoRequest'])->name('customer.toko.submit');
    Route::get('/toko/status', [CustomerController::class, 'viewTokoStatus'])->name('customer.toko.status');
    Route::get('/keranjang/data', [CustomerController::class, 'getKeranjangData'])->name('customer.keranjang.data');
});

// Rute untuk pemilik toko dengan middleware autentikasi dan peran
Route::middleware(['auth', 'role:pemilik_toko'])->prefix('pemilik-toko')->name('pemilik-toko.')->group(function () {
    Route::get('/dashboard', function() {
        return view('pemilik-toko.dashboard');
    })->name('dashboard');
    Route::get('/kategori', [PemilikTokoController::class, 'manageKategori'])->name('kategori');
    Route::post('/kategori/store', [PemilikTokoController::class, 'storeKategori'])->name('kategori.store');
    Route::get('/kategori/{id}/edit', [PemilikTokoController::class, 'editKategori'])->name('kategori.edit');
    Route::patch('/kategori/{id}', [PemilikTokoController::class, 'updateKategori'])->name('kategori.update');
    Route::delete('/kategori/{id}', [PemilikTokoController::class, 'deleteKategori'])->name('kategori.delete');
    Route::get('/shipping', [PemilikTokoController::class, 'manageShippingOrders'])->name('shipping');
    Route::patch('/shipping/{id}/status', [PemilikTokoController::class, 'updateShippingStatus'])->name('shipping.status');
    Route::post('/buy-from-kategori/{kategori_id}', [CustomerController::class, 'buyFromKategori'])->name('buy.from.kategori');
    Route::post('/buy-from-toko-kategori/{kategori_id}', [CustomerController::class, 'buyFromTokoKategori'])->name('buy.from.toko.kategori');
    Route::get('/keranjang', [CustomerController::class, 'keranjang'])->name('keranjang');
    Route::patch('/keranjang/update/{keranjang_id}', [CustomerController::class, 'updateKeranjang'])->name('keranjang.update');
    Route::delete('/keranjang/hapus/{keranjang_id}', [CustomerController::class, 'hapusKeranjang'])->name('keranjang.hapus');
    Route::post('/checkout', [CustomerController::class, 'checkout'])->name('checkout');
    Route::get('/order-history', [CustomerController::class, 'orderHistory'])->name('order.history');
    Route::post('/cancel-order/{id}', [CustomerController::class, 'cancelOrder'])->name('cancel.order');
    Route::get('/download-laporan/{transaksi_id}', [CustomerController::class, 'downloadLaporan'])->name('download.laporan');
    Route::post('/feedback', [CustomerController::class, 'submitFeedback'])->name('feedback');
    Route::get('/keranjang/data', [CustomerController::class, 'getKeranjangData'])->name('keranjang.data');
});

// Rute publik untuk pengunjung melihat kategori
Route::get('/kategori/view/{id}', function($id) {
    return redirect()->route('home', ['kategori_id' => $id]);
})->name('kategori.view.guest');

// Rute autentikasi
require __DIR__.'/auth.php';

// Rute fallback untuk menangani URL yang tidak ditemukan
Route::fallback(function () {
    return redirect()->route('home');
});