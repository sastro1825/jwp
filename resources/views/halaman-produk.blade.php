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
                @if(auth()->check() && auth()->user()->role === 'customer')
                    <form action="{{ route('customer.keranjang.tambah', $produk->id) }}" method="POST">
                        @csrf
                        <input type="number" name="jumlah" value="1" min="1">
                        <button type="submit" class="btn btn-success">Beli</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Login untuk Beli</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    {{ $produks->links() }} <!-- Pagination, sekarang aman karena paginate() -->
</div>
@endsection