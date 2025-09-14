@extends('layouts.app')

@section('content')
{{-- Halaman Produk untuk Customer yang sudah login --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Halaman Produk - Toko Alat Kesehatan</h1>
            <p class="text-muted">Selamat datang, {{ auth()->user()->name }}! Pilih produk kesehatan yang Anda butuhkan.</p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Menu Customer --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Menu Customer</h5>
                </div>
                <div>
                    <a href="{{ route('customer.keranjang') }}" class="btn btn-primary me-2">
                        <i class="bi bi-cart3"></i> Keranjang Belanja
                    </a>
                    <a href="{{ route('customer.account') }}" class="btn btn-secondary">
                        <i class="bi bi-person-circle"></i> My Account
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tampilkan Kategori dengan Harga --}}
    @if($kategoris->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <h3>Kategori Produk</h3>
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
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Daftar Produk --}}
    <div class="row">
        <div class="col-12">
            <h3>Produk Tersedia</h3>
            <p class="text-muted mb-4">Total {{ $produks->total() }} produk tersedia untuk Anda</p>
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

                    <div class="card-body d-flex flex-column">
                        {{-- Nama Produk dan ID --}}
                        <h5 class="card-title">{{ $produk->nama }}</h5>
                        <p class="card-text text-muted">ID: {{ $produk->id_produk }}</p>
                        
                        {{-- Harga Produk --}}
                        <h4 class="text-success mb-3">
                            Rp {{ number_format($produk->harga, 0, ',', '.') }}
                        </h4>
                        
                        {{-- Deskripsi jika ada --}}
                        @if($produk->deskripsi)
                            <p class="card-text flex-grow-1">{{ Str::limit($produk->deskripsi, 100) }}</p>
                        @endif

                        {{-- Info Toko --}}
                        <small class="text-muted mb-3">
                            <i class="bi bi-shop"></i> {{ $produk->toko->nama ?? 'Toko Resmi' }}
                        </small>
                        
                        {{-- Form Beli untuk Customer --}}
                        <div class="mt-auto">
                            <form action="{{ route('customer.keranjang.tambah', $produk->id) }}" method="POST">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-4">
                                        <input type="number" 
                                               name="jumlah" 
                                               value="1" 
                                               min="1" 
                                               max="10" 
                                               class="form-control form-control-sm">
                                    </div>
                                    <div class="col-8">
                                        <button type="submit" class="btn btn-success btn-sm w-100">
                                            <i class="bi bi-cart-plus"></i> Beli Sekarang
                                        </button>
                                    </div>
                                </div>
                            </form>
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

    {{-- Section Feedback untuk Customer --}}
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-heart"></i> Berikan Feedback Anda
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Bagaimana pengalaman berbelanja Anda? Berikan masukan untuk meningkatkan layanan kami.</p>
                    <form action="{{ route('customer.feedback') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="message" class="form-label">Pesan Feedback <span class="text-danger">*</span></label>
                            <textarea id="message" 
                                      name="message" 
                                      class="form-control @error('message') is-invalid @enderror" 
                                      rows="4" 
                                      placeholder="Tuliskan feedback, saran, atau kritik Anda di sini..."
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Kirim Feedback
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection