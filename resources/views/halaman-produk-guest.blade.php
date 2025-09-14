@extends('layouts.app')

@section('content')
{{-- Halaman Produk untuk Visitor/Guest --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Halaman Produk - Toko Alat Kesehatan</h1>
            <p class="text-muted">Selamat datang di OSS! Silakan <a href="{{ route('register') }}" class="text-primary">daftar</a> atau <a href="{{ route('login') }}" class="text-primary">login</a> untuk mulai berbelanja.</p>
        </div>
    </div>

    {{-- Call to Action untuk Guest --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Belum punya akun?</strong> Daftar sekarang untuk mulai berbelanja dan nikmati kemudahan transaksi online!
                </div>
                <div>
                    <a href="{{ route('register') }}" class="btn btn-primary me-2">
                        <i class="bi bi-person-plus"></i> Daftar Sekarang
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tampilkan Kategori dengan Harga untuk Guest --}}
    @if($kategoris->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <h3>Kategori Produk</h3>
                <p class="text-muted">Jelajahi berbagai kategori alat kesehatan yang tersedia</p>
                <div class="row">
                    @foreach($kategoris as $kategori)
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center">
                                {{-- Gambar Kategori --}}
                                @if($kategori->gambar)
                                    <img src="{{ Storage::url($kategori->gambar) }}" 
                                         alt="{{ $kategori->nama }}" 
                                         class="img-fluid mb-3"
                                         style="max-height: 100px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center mb-3" 
                                         style="height: 100px; border-radius: 5px;">
                                        <i class="bi bi-heart-pulse text-primary" style="font-size: 3rem;"></i>
                                    </div>
                                @endif
                                
                                <h5 class="card-title text-primary">{{ $kategori->nama }}</h5>
                                <p class="card-text">{{ $kategori->deskripsi }}</p>
                                
                                {{-- Harga Kategori --}}
                                @if($kategori->harga)
                                    <div class="mb-2">
                                        <span class="badge bg-success fs-6">
                                            Mulai Rp {{ number_format($kategori->harga, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Ajakan Login untuk melihat produk --}}
                                <small class="text-muted">
                                    <i class="bi bi-lock"></i> Login untuk melihat produk dalam kategori ini
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Daftar Produk untuk Guest (Limited View) --}}
    <div class="row">
        <div class="col-12">
            <h3>Preview Produk</h3>
            <p class="text-muted">Berikut beberapa produk yang tersedia. Login untuk melihat harga dan berbelanja!</p>
        </div>
    </div>

    @if($produks->count() > 0)
        <div class="row">
            @foreach($produks as $produk)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    {{-- Badge Kategori --}}
                    <div class="position-absolute top-0 start-0 m-2">
                        <span class="badge bg-primary">{{ $produk->kategori->nama }}</span>
                    </div>

                    {{-- Overlay untuk Guest --}}
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge bg-warning">
                            <i class="bi bi-lock"></i> Login Required
                        </span>
                    </div>

                    <div class="card-body d-flex flex-column">
                        {{-- Nama Produk dan ID --}}
                        <h5 class="card-title">{{ $produk->nama }}</h5>
                        <p class="card-text text-muted">ID: {{ $produk->id_produk }}</p>
                        
                        {{-- Harga Produk (disembunyikan untuk guest) --}}
                        <div class="mb-3">
                            <div class="bg-light p-3 text-center rounded">
                                <i class="bi bi-eye-slash text-muted"></i>
                                <p class="text-muted mb-0">Harga tersedia setelah login</p>
                            </div>
                        </div>
                        
                        {{-- Deskripsi singkat --}}
                        @if($produk->deskripsi)
                            <p class="card-text flex-grow-1">{{ Str::limit($produk->deskripsi, 80) }}</p>
                        @endif

                        {{-- Info Toko --}}
                        <small class="text-muted mb-3">
                            <i class="bi bi-shop"></i> {{ $produk->toko->nama ?? 'Toko Resmi' }}
                        </small>
                        
                        {{-- Tombol Login untuk Guest --}}
                        <div class="mt-auto">
                            <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Login untuk Beli
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                {{ $produks->links() }}
            </div>
        </div>
    @else
        {{-- Tampilan jika tidak ada produk --}}
        <div class="text-center py-5">
            <i class="bi bi-box" style="font-size: 4rem; color: #ccc;"></i>
            <h4 class="mt-3">Belum Ada Produk</h4>
            <p class="text-muted">Produk sedang dalam proses update. Silakan cek kembali nanti.</p>
        </div>
    @endif

    {{-- Benefits Section untuk Guest --}}
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3 class="text-center mb-4">Mengapa Berbelanja di OSS?</h3>
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <i class="bi bi-shield-check" style="font-size: 3rem;"></i>
                            <h5 class="mt-2">Produk Terjamin</h5>
                            <p>Alat kesehatan berkualitas dan bergaransi resmi</p>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <i class="bi bi-truck" style="font-size: 3rem;"></i>
                            <h5 class="mt-2">Pengiriman Cepat</h5>
                            <p>Kirim ke seluruh Indonesia dengan tracking</p>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <i class="bi bi-credit-card" style="font-size: 3rem;"></i>
                            <h5 class="mt-2">Pembayaran Mudah</h5>
                            <p>Kartu kredit, debit, PayPal atau bayar di tempat</p>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <i class="bi bi-headset" style="font-size: 3rem;"></i>
                            <h5 class="mt-2">Customer Support</h5>
                            <p>Tim support siap membantu 24/7</p>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ route('register') }}" class="btn btn-light btn-lg me-3">
                            <i class="bi bi-person-plus"></i> Daftar Gratis Sekarang
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Sudah Punya Akun? Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Testimonials Section --}}
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="text-center mb-4">Apa Kata Customer Kami?</h3>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <p class="mt-3">"Produk berkualitas dan pengiriman cepat. Sangat recommended!"</p>
                            <small class="text-muted">- Customer A</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <p class="mt-3">"Harga terjangkau dengan kualitas yang tidak diragukan lagi."</p>
                            <small class="text-muted">- Customer B</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <p class="mt-3">"Customer service yang ramah dan responsif. Terima kasih OSS!"</p>
                            <small class="text-muted">- Customer C</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection