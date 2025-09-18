<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

// Kelas middleware untuk memeriksa peran pengguna
class CheckRole
{
    // Menangani permintaan masuk dan memeriksa peran
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Memeriksa apakah pengguna sudah login
        if (!Auth::check()) {
            // Mengarahkan ke halaman login dengan pesan error
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Mendapatkan data pengguna yang sedang login
        $user = Auth::user();
        
        // Memeriksa apakah peran pengguna sesuai dengan parameter
        if ($user->role !== $role) {
            // Mengarahkan berdasarkan peran pengguna dengan pesan error
            if ($user->role === 'admin') {
                // Mengarahkan admin ke dashboard admin
                return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak. Anda adalah admin.');
            } elseif ($user->role === 'customer') {
                // Mengarahkan customer ke area customer
                return redirect()->route('customer.area')->with('error', 'Akses ditolak. Anda adalah customer.');
            } elseif ($user->role === 'pemilik_toko') {
                // Mengarahkan pemilik toko ke area customer
                return redirect()->route('customer.area')->with('error', 'Akses sebagai pemilik toko, masuk ke customer area.');
            }
            
            // Menghentikan akses jika peran tidak sesuai
            abort(403, 'Akses ditolak. Role tidak sesuai.');
        }

        // Melanjutkan permintaan jika peran sesuai
        return $next($request);
    }
}