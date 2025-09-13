<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;

class HomeController extends Controller
{
    public function index()
    {
        $kategoris = Kategori::all();
        $produks = Produk::with(['kategori', 'toko'])->paginate(6); // Pastikan paginate() return LengthAwarePaginator
        return view('halaman-produk', compact('produks', 'kategoris'));
    }
}