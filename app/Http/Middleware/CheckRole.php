<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request - support untuk role pemilik_toko
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();
        
        // Cek role user sesuai parameter
        if ($user->role !== $role) {
            // Jika role tidak sesuai, redirect ke halaman yang sesuai dengan role mereka
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak. Anda adalah admin.');
            } elseif ($user->role === 'customer') {
                return redirect()->route('home')->with('error', 'Akses ditolak. Anda adalah customer.');
            } elseif ($user->role === 'pemilik_toko') {
                return redirect()->route('pemilik-toko.dashboard')->with('error', 'Akses ditolak. Anda adalah pemilik toko.');
            }
            
            // Default redirect jika role tidak dikenali
            abort(403, 'Akses ditolak. Role tidak sesuai.');
        }

        return $next($request);
    }
}