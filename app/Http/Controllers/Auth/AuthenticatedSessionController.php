<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Kelas untuk mengelola sesi autentikasi
class AuthenticatedSessionController extends Controller
{
    // Menampilkan halaman login
    public function create(): View
    {
        return view('auth.login'); // Mengembalikan view login
    }

    // Menangani permintaan autentikasi dan redirect berdasarkan role
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(); // Melakukan autentikasi berdasarkan data request

        $request->session()->regenerate(); // Meregenerasi sesi setelah login

        $user = Auth::user(); // Mendapatkan data pengguna yang sedang login
        
        // Mengarahkan pengguna berdasarkan role
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard'); // Redirect admin ke dashboard
        } elseif ($user->role === 'customer') {
            return redirect()->route('customer.area'); // Redirect customer ke area customer
        } elseif ($user->role === 'pemilik_toko') {
            return redirect()->route('customer.area'); // Redirect pemilik toko ke area customer
        }

        return redirect()->route('customer.area'); // Redirect default ke area customer
    }

    // Menghapus sesi autentikasi (logout)
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout(); // Melakukan logout dari guard web

        $request->session()->invalidate(); // Mengakhiri sesi saat ini

        $request->session()->regenerateToken(); // Meregenerasi token sesi

        return redirect('/'); // Redirect ke halaman utama
    }
}