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
        $produks = Produk::with(['kategori', 'toko'])->paginate(6);

        if (auth()->check() && auth()->user()->role === 'customer') {
            return view('halaman-produk-customer', compact('produks', 'kategoris'));
        }

        return view('halaman-produk-guest', compact('produks', 'kategoris'));
    }
}