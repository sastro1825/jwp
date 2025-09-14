<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;

class HomeController extends Controller
{
    /**
     * Tampilkan halaman utama dengan kategori dan produk
     */
    public function index(Request $request)
    {
        // Ambil semua kategori untuk sidebar
        $kategoris = Kategori::all();
        
        // Filter produk berdasarkan kategori jika ada parameter
        $produks = Produk::with(['kategori', 'toko']);
        
        // Jika ada filter kategori dari request
        if ($request->has('kategori_id') && $request->kategori_id) {
            $produks = $produks->where('kategori_id', $request->kategori_id);
        }
        
        // Pagination untuk produk
        $produks = $produks->paginate(6);
        
        // Tentukan view berdasarkan role user
        if (auth()->check() && auth()->user()->role === 'customer') {
            return view('halaman-produk-customer', compact('produks', 'kategoris'));
        }
        
        return view('halaman-produk-guest', compact('produks', 'kategoris'));
    }
    
    /**
     * Tampilkan detail produk untuk modal view
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
}