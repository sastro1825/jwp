<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\GuestBook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Kontroler untuk mengelola halaman utama dan fungsi terkait
class HomeController extends Controller
{
    // Fungsi helper untuk validasi dan preload gambar kategori toko
    private function getValidatedKategoriToko()
    {
        return \App\Models\TokoKategori::with('toko') // Ambil data kategori toko beserta relasi toko
            ->whereHas('toko', function($query) {
                $query->where('status', 'approved'); // Hanya ambil toko dengan status disetujui
            })
            ->get()
            ->map(function($kategori) {
                // Validasi keberadaan file gambar
                if ($kategori->gambar) {
                    $fullPath = storage_path('app/public/' . $kategori->gambar); // Path lengkap file gambar
                    $kategori->image_valid = file_exists($fullPath) && filesize($fullPath) > 0; // Cek apakah gambar ada dan valid
                    $kategori->image_url = asset('storage/' . $kategori->gambar); // URL untuk akses gambar
                    
                    // Log informasi untuk debugging
                    \Log::info('Image validation', [
                        'kategori' => $kategori->nama, // Nama kategori
                        'path' => $kategori->gambar, // Path gambar
                        'full_path' => $fullPath, // Path lengkap gambar
                        'exists' => file_exists($fullPath), // Status keberadaan file
                        'size' => file_exists($fullPath) ? filesize($fullPath) : 0, // Ukuran file
                        'valid' => $kategori->image_valid // Status validitas gambar
                    ]);
                } else {
                    $kategori->image_valid = false; // Gambar tidak valid jika tidak ada
                    $kategori->image_url = null; // URL gambar diset null
                }
                return $kategori; // Kembalikan objek kategori yang telah diproses
            })
            ->filter(function($kategori) {
                // Hanya kembalikan kategori dengan toko valid dan disetujui
                return $kategori->toko && $kategori->toko->status === 'approved';
            });
    }

    // Menampilkan halaman utama untuk pengguna tamu
    public function index()
    {
        // Cek apakah pengguna sudah login
        if (Auth::check()) {
            $user = Auth::user(); // Ambil data pengguna yang sedang login
            
            // Arahkan pengguna ke halaman sesuai peran
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard'); // Redirect admin ke dashboard
            } elseif ($user->role === 'customer') {
                return redirect()->route('customer.area'); // Redirect pelanggan ke area pelanggan
            } elseif ($user->role === 'pemilik_toko') {
                return redirect()->route('customer.area'); // Redirect pemilik toko ke area pelanggan
            }
        }

        // Ambil semua kategori admin
        $kategoris = Kategori::all();
        
        // Ambil kategori toko dengan relasi toko yang sudah disetujui
        $kategoriToko = \App\Models\TokoKategori::with('toko')
            ->whereHas('toko', function($query) {
                $query->where('status', 'approved'); // Hanya toko dengan status disetujui
            })
            ->get();

        // Tampilkan view halaman produk untuk tamu
        return view('halaman-produk-guest', compact('kategoris', 'kategoriToko'));
    }

    // Menampilkan detail produk melalui AJAX untuk semua pengguna
    public function viewProduk($id)
    {
        try {
            // Ambil data produk beserta relasi kategori dan toko
            $produk = \App\Models\Produk::with(['kategori', 'toko'])->findOrFail($id);
            
            // Kembalikan response JSON dengan detail produk
            return response()->json([
                'success' => true,
                'produk' => [
                    'id' => $produk->id, // ID produk
                    'nama' => $produk->nama, // Nama produk
                    'id_produk' => $produk->id_produk, // ID unik produk
                    'harga' => $produk->harga, // Harga produk
                    'deskripsi' => $produk->deskripsi, // Deskripsi produk
                    'kategori' => $produk->kategori->nama ?? 'Tidak Ada Kategori', // Nama kategori atau default
                    'toko' => $produk->toko->nama ?? 'Tidak Ada Toko', // Nama toko atau default
                    'gambar' => $produk->gambar ? asset('storage/' . $produk->gambar) : null, // URL gambar produk
                ]
            ]);
        } catch (\Exception $e) {
            // Kembalikan response JSON jika produk tidak ditemukan
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
    }

    // Menampilkan produk berdasarkan kategori untuk filter
    public function viewKategoriProduk($id)
    {
        try {
            // Ambil data kategori beserta produk terkait
            $kategori = Kategori::with('produks')->findOrFail($id);
            $kategoris = Kategori::all(); // Ambil semua kategori untuk sidebar
            
            // Ambil kategori toko dengan validasi gambar
            $kategoriToko = $this->getValidatedKategoriToko();
            
            // Tentukan view berdasarkan status login pengguna
            if (Auth::check()) {
                return view('halaman-produk-customer', compact('kategoris', 'kategori', 'kategoriToko')); // View untuk pengguna terautentikasi
            } else {
                return view('halaman-produk-guest', compact('kategoris', 'kategori', 'kategoriToko')); // View untuk tamu
            }
        } catch (\Exception $e) {
            // Redirect ke halaman utama jika kategori tidak ditemukan
            return redirect()->route('home')->with('error', 'Kategori tidak ditemukan');
        }
    }

    // Menyimpan feedback dari pengguna tamu tanpa login
    public function submitGuestFeedback(Request $request)
    {
        try {
            // Validasi input feedback
            $validated = $request->validate([
                'name' => 'required|string|max:100|min:2', // Nama wajib, 2-100 karakter
                'email' => 'required|email|max:100', // Email wajib, format valid
                'message' => 'required|string|max:1000|min:10', // Pesan wajib, 10-1000 karakter
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

            // Simpan feedback ke database
            GuestBook::create([
                'name' => $validated['name'], // Nama pengirim
                'email' => $validated['email'], // Email pengirim
                'message' => $validated['message'], // Pesan feedback
                'status' => 'pending', // Status default untuk moderasi
                'user_id' => null, // Null untuk pengguna tamu
            ]);

            // Redirect ke halaman utama dengan pesan sukses
            return redirect()->route('home')->with('success', 
                'Terima kasih ' . $validated['name'] . '! Feedback Anda telah dikirim dan akan dimoderasi oleh admin.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Redirect dengan pesan error validasi
            return redirect()->route('home')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            // Log error dan redirect dengan pesan error umum
            Log::error('Error submitting guest feedback: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 
                'Terjadi kesalahan saat mengirim feedback. Silakan coba lagi.');
        }
    }
}