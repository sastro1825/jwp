<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route utama OSS - tampilkan halaman produk untuk visitor/customer
// Menampilkan kategori produk dengan gambar, harga, view, buy sesuai SRS
Route::get('/', [HomeController::class, 'index'])->name('home');

// Route untuk view detail produk via AJAX - untuk semua user (guest & customer)
Route::get('/produk/view/{id}', [HomeController::class, 'viewProduk'])->name('produk.view');

// Route untuk filter kategori - untuk semua user
Route::get('/kategori/{id}', [HomeController::class, 'index'])->name('kategori.filter');

// Route dashboard default Breeze - untuk user yang sudah login
Route::get('/dashboard', function () {
    // Redirect berdasarkan role user
    if (auth()->check()) {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->role === 'customer') {
            return redirect()->route('home');
        }
    }
    return view('dashboard');
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Routes untuk admin dengan middleware auth dan role admin
| Admin dapat mengelola customers, kategori, toko, feedback, dan shipping
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard admin - menampilkan statistik dan menu utama
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Manage Customers - CRUD customer sesuai SRS requirement
    Route::get('/customers', [AdminController::class, 'manageCustomers'])->name('customers');
    Route::get('/customers/{id}/edit', [AdminController::class, 'editCustomer'])->name('customers.edit');
    Route::patch('/customers/{id}', [AdminController::class, 'updateCustomer'])->name('customers.update');
    Route::delete('/customers/{id}', [AdminController::class, 'deleteCustomer'])->name('customers.delete');
    
    // Manage Kategori - Add/Remove/Update Item Category sesuai SRS
    Route::get('/kategori', [AdminController::class, 'manageKategori'])->name('kategori');
    Route::post('/kategori/store', [AdminController::class, 'storeKategori'])->name('kategori.store');
    Route::get('/kategori/{id}/edit', [AdminController::class, 'editKategori'])->name('kategori.edit');
    Route::patch('/kategori/{id}', [AdminController::class, 'updateKategori'])->name('kategori.update');
    Route::delete('/kategori/{id}', [AdminController::class, 'deleteKategori'])->name('kategori.delete');
    
    // Manage Toko Requests - Approve/Reject Shop Creation Request sesuai SRS
    Route::get('/toko/requests', [AdminController::class, 'manageTokoRequests'])->name('toko.requests');
    Route::post('/toko/approve/{id}', [AdminController::class, 'approveToko'])->name('toko.approve');
    Route::post('/toko/reject/{id}', [AdminController::class, 'rejectToko'])->name('toko.reject');
    
    // Manage Guest Book - View/Delete Guest Book Entries sesuai SRS
    Route::get('/guestbook', [AdminController::class, 'manageGuestBook'])->name('guestbook');
    Route::post('/guestbook/approve/{id}', [AdminController::class, 'approveFeedback'])->name('guestbook.approve');
    Route::post('/guestbook/reject/{id}', [AdminController::class, 'rejectFeedback'])->name('guestbook.reject');
    Route::delete('/guestbook/{id}', [AdminController::class, 'deleteFeedback'])->name('guestbook.delete');
    
    // Manage Shipping Orders - kelola pengiriman sesuai SRS
    Route::get('/shipping', [AdminController::class, 'manageShippingOrders'])->name('shipping');
    Route::get('/shipping/create/{transaksi_id}', [AdminController::class, 'createShippingOrder'])->name('shipping.create');
    Route::post('/shipping/store', [AdminController::class, 'storeShippingOrder'])->name('shipping.store');
    Route::patch('/shipping/{id}/status', [AdminController::class, 'updateShippingStatus'])->name('shipping.status');
});

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
| Routes untuk customer dengan middleware auth dan role customer
| Customer dapat mengelola keranjang unified, checkout, feedback, dan account
*/
Route::middleware(['auth', 'role:customer'])->name('customer.')->group(function () {
    
    // Buy dari kategori - untuk tombol buy langsung dari kategori
    Route::post('/buy-from-kategori/{kategori_id}', [CustomerController::class, 'buyFromKategori'])->name('buy.from.kategori');
    
    // Keranjang unified - Add/Remove item from Cart sesuai SRS (produk dan kategori)
    Route::post('/keranjang/tambah/{produk_id}', [CustomerController::class, 'tambahKeKeranjang'])->name('keranjang.tambah');
    Route::get('/keranjang', [CustomerController::class, 'keranjang'])->name('keranjang');
    Route::patch('/keranjang/update/{keranjang_id}', [CustomerController::class, 'updateKeranjang'])->name('keranjang.update');
    Route::delete('/keranjang/hapus/{keranjang_id}', [CustomerController::class, 'hapusKeranjang'])->name('keranjang.hapus');
    
    // Buy langsung produk - untuk tombol buy pada produk
    Route::post('/buy-langsung/{produk_id}', [CustomerController::class, 'buyLangsung'])->name('buy.langsung');
    
    // Checkout unified - Payment dengan prepaid/postpaid sesuai SRS
    Route::post('/checkout', [CustomerController::class, 'checkout'])->name('checkout');
    
    // Account management routes
    Route::post('/cancel-order/{id}', [CustomerController::class, 'cancelOrder'])->name('cancel.order');
    Route::get('/download-laporan/{transaksi_id}', [CustomerController::class, 'downloadLaporan'])->name('download.laporan');
    Route::post('/feedback', [CustomerController::class, 'submitFeedback'])->name('feedback');
    Route::get('/account', [CustomerController::class, 'viewAccount'])->name('account');
});

/*
|--------------------------------------------------------------------------
| Public Routes (Guest/Visitor)
|--------------------------------------------------------------------------
| Routes yang bisa diakses semua user tanpa login
| Visitor dapat melihat kategori tapi harus login untuk buy
*/

// Route untuk visitor yang ingin melihat detail kategori tanpa login
Route::get('/kategori/view/{id}', function($id) {
    // Redirect guest ke halaman utama dengan filter kategori
    return redirect()->route('home', ['kategori_id' => $id]);
})->name('kategori.view');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
| Routes untuk login, register, password reset, profile management
| Menggunakan Laravel Breeze authentication
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
| Route fallback untuk handle 404 - redirect ke home
*/
Route::fallback(function () {
    return redirect()->route('home');
});