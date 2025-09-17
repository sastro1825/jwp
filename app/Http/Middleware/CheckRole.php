<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request - support untuk role pemilik_toko dan COD admin
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();
        
        // Cek role user sesuai parameter
        if ($user->role !== $role) {
            // Redirect berdasarkan role dengan pesan yang sesuai
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak. Anda adalah admin.');
            } elseif ($user->role === 'customer') {
                return redirect()->route('customer.area')->with('error', 'Akses ditolak. Anda adalah customer.');
            } elseif ($user->role === 'pemilik_toko') {
                return redirect()->route('customer.area')->with('error', 'Akses sebagai pemilik toko, masuk ke customer area.');
            }
            
            abort(403, 'Akses ditolak. Role tidak sesuai.');
        }

        return $next($request);
    }
}