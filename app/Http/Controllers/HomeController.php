<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;

class HomeController extends Controller
{
    /**
     * Tampilkan halaman utama dengan HANYA kategori produk (tanpa produk tersedia)
     * Menampilkan kategori dengan gambar, harga, view, buy sesuai SRS
     */
    public function index(Request $request)
    {
        // Ambil semua kategori dengan relasi produk untuk hitung jumlah
        $kategoris = Kategori::with(['produks'])->get();
        
        // Tidak perlu ambil produk sama sekali - hanya kategori yang ditampilkan
        $produks = collect(); // Empty collection
        
        // Tentukan view berdasarkan role user
        if (auth()->check() && auth()->user()->role === 'customer') {
            return view('halaman-produk-customer', compact('kategoris', 'produks'));
        }
        
        return view('halaman-produk-guest', compact('kategoris', 'produks'));
    }
    
    /**
     * Tampilkan detail produk untuk modal view
     * AJAX endpoint untuk view detail produk
     */
    public function viewProduk($id)
    {
        // Cari produk berdasarkan ID dengan relasi
        $produk = Produk::with(['kategori', 'toko'])->findOrFail($id);
        
        // Return JSON untuk AJAX request
        return response()->json([
            'id' => $produk->id,
            'nama' => $produk->nama,
            'id_produk' => $produk->id_produk,
            'harga' => $produk->harga,
            'harga_formatted' => 'Rp ' . number_format($produk->harga, 0, ',', '.'),
            'deskripsi' => $produk->deskripsi ?? 'Tidak ada deskripsi',
            'kategori' => $produk->kategori->nama,
            'toko' => $produk->toko->nama ?? 'Toko Resmi',
            'gambar' => $produk->gambar ? asset('storage/' . $produk->gambar) : null
        ]);
    }

    /**
     * Tampilkan produk dalam kategori tertentu
     * Digunakan ketika user klik kategori untuk lihat produk di dalamnya
     */
    public function viewKategoriProduk($kategori_id)
    {
        // Ambil kategori yang dipilih
        $kategori = Kategori::findOrFail($kategori_id);
        
        // Ambil semua kategori untuk sidebar
        $kategoris = Kategori::with(['produks'])->get();
        
        // Ambil produk dalam kategori yang dipilih
        $produks = Produk::with(['kategori', 'toko'])
                         ->where('kategori_id', $kategori_id)
                         ->paginate(6);
        
        // Tentukan view berdasarkan role user
        if (auth()->check() && auth()->user()->role === 'customer') {
            return view('halaman-produk-customer', compact('kategoris', 'produks', 'kategori'));
        }
        
        return view('halaman-produk-guest', compact('kategoris', 'produks', 'kategori'));
    }

    /**
     * Buy langsung dari kategori tanpa lihat produk dulu
     * Ambil produk pertama dari kategori dan masukkan ke keranjang
     */
    public function buyFromKategori($kategori_id)
    {
        // Cek apakah user sudah login dan role customer
        if (!auth()->check() || auth()->user()->role !== 'customer') {
            return redirect()->route('login')->with('error', 'Silakan login sebagai customer untuk berbelanja.');
        }

        // Ambil produk pertama dari kategori ini
        $produk = Produk::where('kategori_id', $kategori_id)->first();
        
        if (!$produk) {
            return redirect()->back()->with('error', 'Tidak ada produk dalam kategori ini.');
        }

        // Redirect ke buy langsung dengan produk pertama dari kategori
        return app(CustomerController::class)->buyLangsung($produk->id);
    }
}