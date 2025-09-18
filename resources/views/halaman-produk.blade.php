@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Menampilkan judul halaman --}}
    <h1>Halaman Produk - Toko Alat Kesehatan</h1>
    {{-- Looping untuk menampilkan nama kategori --}}
    @foreach($kategoris as $kategori)
    <h3>{{ $kategori->nama }}</h3>
    @endforeach
    <div class="row">
        {{-- Looping untuk menampilkan data produk --}}
        @foreach($produks as $produk)
        <div class="col-md-4">
            <div class="card">
                {{-- Menampilkan nama dan ID produk --}}
                <h5>{{ $produk->nama }} ({{ $produk->id_produk }})</h5>
                {{-- Menampilkan harga produk dengan format rupiah --}}
                <p>Rp. {{ number_format($produk->harga, 0, ',', '.') }}</p>
                {{-- Cek autentikasi dan role pengguna untuk menampilkan form atau tombol login --}}
                @if(auth()->check() && auth()->user()->role === 'customer')
                    {{-- Form untuk menambah produk ke keranjang --}}
                    <form action="{{ route('customer.keranjang.tambah', $produk->id) }}" method="POST">
                        {{-- Token CSRF untuk keamanan form --}}
                        @csrf
                        {{-- Input jumlah produk yang akan dibeli --}}
                        <input type="number" name="jumlah" value="1" min="1">
                        {{-- Tombol untuk submit form pembelian --}}
                        <button type="submit" class="btn btn-success">Beli</button>
                    </form>
                @else
                    {{-- Tombol untuk redirect ke halaman login jika belum login atau bukan customer --}}
                    <a href="{{ route('login') }}" class="btn btn-primary">Login untuk Beli</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    {{-- Menampilkan link paginasi untuk produk --}}
    {{ $produks->links() }}
</div>
@endsection