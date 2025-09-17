<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\GuestBook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Tampilkan halaman home - DENGAN PERBAIKAN PATH GAMBAR
     */
    public function index()
    {
        // Cek apakah user sudah login
        if (Auth::check()) {
            $user = Auth::user();
            
            // Redirect berdasarkan role user yang sudah login
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'customer') {
                return redirect()->route('customer.area');
            } elseif ($user->role === 'pemilik_toko') {
                return redirect()->route('customer.area');
            }
        }

        // Ambil kategori admin untuk guest
        $kategoris = Kategori::all();
        
        // Ambil kategori dari toko dengan perbaikan path gambar
        $kategoriToko = \App\Models\TokoKategori::with('toko')
            ->whereHas('toko', function($query) {
                $query->where('status', 'approved');
            })
            ->get();

        // Debug path gambar untuk troubleshooting - PERBAIKAN PATH GAMBAR
        foreach($kategoriToko as $kategori) {
            \Log::info('Debug Kategori Toko Gambar', [
                'id' => $kategori->id,
                'nama' => $kategori->nama,
                'gambar_path' => $kategori->gambar,
                'full_url' => $kategori->gambar ? asset('storage/' . $kategori->gambar) : null,
                'file_exists' => $kategori->gambar ? \Storage::disk('public')->exists($kategori->gambar) : false
            ]);
        }

        return view('halaman-produk-guest', compact('kategoris', 'kategoriToko'));
    }

    /**
     * View detail produk via AJAX untuk semua user
     */
    public function viewProduk($id)
    {
        try {
            $produk = \App\Models\Produk::with(['kategori', 'toko'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'produk' => [
                    'id' => $produk->id,
                    'nama' => $produk->nama,
                    'id_produk' => $produk->id_produk,
                    'harga' => $produk->harga,
                    'deskripsi' => $produk->deskripsi,
                    'kategori' => $produk->kategori->nama ?? 'Tidak Ada Kategori',
                    'toko' => $produk->toko->nama ?? 'Tidak Ada Toko',
                    'gambar' => $produk->gambar ? asset('storage/' . $produk->gambar) : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
    }

    /**
     * View kategori produk untuk filter
     */
    public function viewKategoriProduk($id)
    {
        try {
            $kategori = Kategori::with('produks')->findOrFail($id);
            $kategoris = Kategori::all(); // Untuk sidebar
            
            // Cek status login untuk menentukan view
            if (Auth::check()) {
                return view('halaman-produk-customer', compact('kategoris', 'kategori'));
            } else {
                return view('halaman-produk-guest', compact('kategoris', 'kategori'));
            }
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Kategori tidak ditemukan');
        }
    }

    /**
     * Submit feedback dari guest/visitor tanpa login
     */
    public function submitGuestFeedback(Request $request)
    {
        try {
            // Validasi input feedback guest
            $validated = $request->validate([
                'name' => 'required|string|max:100|min:2',
                'email' => 'required|email|max:100',
                'message' => 'required|string|max:1000|min:10',
            ], [
                'name.required' => 'Nama wajib diisi.',
                'name.min' => 'Nama minimal 2 karakter.',
                'name.max' => 'Nama maksimal 100 karakter.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal 100 karakter.',
                'message.required' => 'Pesan feedback wajib diisi.',
                'message.min' => 'Pesan minimal 10 karakter.',
                'message.max' => 'Pesan maksimal 1000 karakter.',
            ]);

            // Simpan feedback guest ke database
            GuestBook::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'message' => $validated['message'],
                'status' => 'pending', // Default pending untuk moderasi admin
                'user_id' => null, // Null untuk visitor/guest
            ]);

            return redirect()->route('home')->with('success', 
                'Terima kasih ' . $validated['name'] . '! Feedback Anda telah dikirim dan akan dimoderasi oleh admin.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('home')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error submitting guest feedback: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 
                'Terjadi kesalahan saat mengirim feedback. Silakan coba lagi.');
        }
    }
}