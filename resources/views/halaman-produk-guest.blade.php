@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Halaman Produk - Toko Alat Kesehatan</h1>
    @foreach($kategoris as $kategori)
    <h3>{{ $kategori->nama }}</h3>
    @endforeach
    <div class="row">
        @foreach($produks as $produk)
        <div class="col-md-4">
            <div class="card">
                <h5>{{ $produk->nama }} ({{ $produk->id_produk }})</h5>
                <p>Rp. {{ number_format($produk->harga, 0, ',', '.') }}</p>
                <a href="{{ route('login') }}" class="btn btn-primary">Login untuk Beli</a>
            </div>
        </div>
        @endforeach
    </div>
    {{ $produks->links() }}
</div>
@endsection