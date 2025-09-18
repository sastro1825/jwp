<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
      protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class, // Middleware untuk mempercayai host tertentu (dinonaktifkan)
        \App\Http\Middleware\TrustProxies::class, // Middleware untuk mempercayai proxy
        \Illuminate\Http\Middleware\HandleCors::class, // Middleware untuk menangani CORS
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class, // Middleware untuk mencegah permintaan saat mode pemeliharaan
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class, // Middleware untuk memvalidasi ukuran data POST
        \App\Http\Middleware\TrimStrings::class, // Middleware untuk menghapus spasi berlebih dari input
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class, // Middleware untuk mengubah string kosong menjadi null
    ];

    // Grup middleware untuk rute tertentu
    protected $middlewareGroups = [
        'web' => [ // Grup middleware untuk rute web
            \App\Http\Middleware\EncryptCookies::class, // Mengenkripsi cookie
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, // Menambahkan cookie yang antri ke respons
            \Illuminate\Session\Middleware\StartSession::class, // Memulai sesi
            \Illuminate\View\Middleware\ShareErrorsFromSession::class, // Membagikan error dari sesi ke view
            \App\Http\Middleware\VerifyCsrfToken::class, // Memverifikasi token CSRF
            \Illuminate\Routing\Middleware\SubstituteBindings::class, // Mengganti binding pada rute
        ],

        'api' => [ // Grup middleware untuk rute API
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, // Memastikan permintaan API stateful (dinonaktifkan)
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api', // Membatasi jumlah permintaan API
            \Illuminate\Routing\Middleware\SubstituteBindings::class, // Mengganti binding pada rute
        ],
    ];

    // Alias untuk middleware yang dapat digunakan pada rute
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class, // Middleware untuk autentikasi pengguna
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class, // Middleware untuk autentikasi dasar
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class, // Middleware untuk autentikasi sesi
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class, // Middleware untuk mengatur header cache
        'can' => \Illuminate\Auth\Middleware\Authorize::class, // Middleware untuk otorisasi akses
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class, // Middleware untuk mengarahkan pengguna terautentikasi
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class, // Middleware untuk meminta konfirmasi kata sandi
        'signed' => \App\Http\Middleware\ValidateSignature::class, // Middleware untuk memvalidasi tanda tangan URL
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class, // Middleware untuk membatasi jumlah permintaan
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class, // Middleware untuk memastikan email terverifikasi
        'role' => \App\Http\Middleware\CheckRole::class, // Middleware untuk memeriksa peran admin/customer
    ];
}