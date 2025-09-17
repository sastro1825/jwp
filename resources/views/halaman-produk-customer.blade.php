@extends('layouts.app')

@section('content')
{{-- Halaman Produk untuk Customer yang sudah login sesuai SRS --}}
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Product Page - Toko Alat Kesehatan</h1>
            <p class="text-muted">
                Selamat datang, {{ auth()->user()->name }}! 
                @if(auth()->user()->role === 'pemilik_toko')
                    Anda login sebagai <span class="badge bg-success">Pemilik Toko</span>. Anda dapat berbelanja dan mengelola toko.
                @else
                    Pilih kategori produk kesehatan yang Anda butuhkan.
                @endif
            </p>
        </div>
    </div>

    {{-- Alert Messages untuk feedback user --}}
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

    {{-- Quick Menu untuk Customer dengan fitur permohonan toko --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-0">
                        @if(auth()->user()->role === 'pemilik_toko')
                            Menu Pemilik Toko (Area Customer)
                        @else
                            Menu Customer
                        @endif
                    </h5>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    {{-- Link ke keranjang belanja dengan badge jumlah item berdasarkan role --}}
                    @if(auth()->user()->role === 'pemilik_toko')
                        <a href="{{ route('pemilik-toko.keranjang') }}" class="btn btn-primary">
                            <i class="bi bi-cart3"></i> Keranjang Belanja
                            @php
                                $jumlahKeranjang = \App\Models\Keranjang::where('user_id', auth()->id())->sum('jumlah');
                            @endphp
                            @if($jumlahKeranjang > 0)
                                <span class="badge bg-danger">{{ $jumlahKeranjang }}</span>
                            @endif
                        </a>
                        
                        {{-- Link ke riwayat pesanan --}}
                        <a href="{{ route('pemilik-toko.order.history') }}" class="btn btn-secondary">
                            <i class="bi bi-clock-history"></i> Riwayat Pesanan
                        </a>
                        
                        {{-- Menu Dashboard Toko untuk pemilik toko --}}
                        <a href="{{ route('pemilik-toko.dashboard') }}" class="btn btn-success">
                            <i class="bi bi-shop"></i> Dashboard Toko
                        </a>
                    @else
                        <a href="{{ route('customer.keranjang') }}" class="btn btn-primary">
                            <i class="bi bi-cart3"></i> Keranjang Belanja
                            @php
                                $jumlahKeranjang = \App\Models\Keranjang::where('user_id', auth()->id())->sum('jumlah');
                            @endphp
                            @if($jumlahKeranjang > 0)
                                <span class="badge bg-danger">{{ $jumlahKeranjang }}</span>
                            @endif
                        </a>
                        
                        {{-- Link ke riwayat pesanan --}}
                        <a href="{{ route('customer.order.history') }}" class="btn btn-secondary">
                            <i class="bi bi-clock-history"></i> Riwayat Pesanan
                        </a>
                        
                        {{-- Menu Toko berdasarkan status user --}}
                        @if(!auth()->user()->hasPendingTokoRequest() && !auth()->user()->hasApprovedToko())
                            <a href="{{ route('customer.toko.request') }}" class="btn btn-warning">
                                <i class="bi bi-shop"></i> Ajukan Toko
                            </a>
                        @else
                            <a href="{{ route('customer.toko.status') }}" class="btn btn-info">
                                <i class="bi bi-shop"></i> Status Toko
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content dengan Layout seperti SRS --}}
    <div class="row">
        {{-- Product Display Area (Kolom Kiri) - HANYA Menampilkan Kategori Produk --}}
        <div class="col-md-9">
            {{-- Tampilkan Kategori Produk dengan Gambar, Harga, View, Buy sesuai SRS --}}
            @if($kategoris->count() > 0)
                <div class="mb-4">
                    <h3>Kategori Produk</h3>
                    <div class="row">
                        {{-- Loop untuk menampilkan setiap kategori --}}
                        @foreach($kategoris as $kategori)
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="card h-100 kategori-card shadow-sm">
                                {{-- Gambar Kategori dengan ukuran tetap - PERBAIKI PATH GAMBAR --}}
                                <div class="position-relative">
                                    @if($kategori->gambar)
                                        <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                                             alt="{{ $kategori->nama }}" 
                                             class="card-img-top"
                                             style="height: 200px; object-fit: cover;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        {{-- Fallback jika gambar error --}}
                                        <div class="card-img-top bg-light align-items-center justify-content-center" 
                                             style="height: 200px; display: none;">
                                            <i class="bi bi-heart-pulse text-primary" style="font-size: 3rem;"></i>
                                        </div>
                                    @else
                                        {{-- Placeholder jika tidak ada gambar --}}
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                             style="height: 200px;">
                                            <i class="bi bi-heart-pulse text-primary" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    
                                    {{-- Badge jenis kategori --}}
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-info">{{ $kategori->getCategoryTypeLabel() }}</span>
                                    </div>
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    {{-- Nama Kategori --}}
                                    <h6 class="card-title text-center">{{ $kategori->nama }}</h6>
                                    
                                    {{-- Harga Patokan --}}
                                    <div class="text-center mb-3">
                                        <span class="badge bg-success fs-6">
                                            Mulai Rp {{ number_format($kategori->harga, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    
                                    {{-- Deskripsi singkat --}}
                                    @if($kategori->deskripsi)
                                        <p class="card-text text-muted small text-center">
                                            {{ Str::limit($kategori->deskripsi, 50) }}
                                        </p>
                                    @endif
                                    
                                    {{-- Tombol View dan Buy sesuai SRS dengan route berdasarkan role --}}
                                    <div class="mt-auto">
                                        <div class="d-grid gap-2">
                                            {{-- Tombol View untuk melihat detail kategori --}}
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    onclick="viewKategori({{ $kategori->id }})">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            
                                            {{-- Tombol Buy untuk buy langsung dari kategori dengan route sesuai role --}}
                                            @if(auth()->user()->role === 'pemilik_toko')
                                                <form action="{{ route('pemilik-toko.buy.from.kategori', $kategori->id) }}" method="POST" style="display: inline;" class="buy-form">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                                        <i class="bi bi-bag-plus"></i> Buy
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('customer.buy.from.kategori', $kategori->id) }}" method="POST" style="display: inline;" class="buy-form">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                                        <i class="bi bi-bag-plus"></i> Buy
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                {{-- Kategori dari Toko-toko (Database Terpisah) --}}
                @if(isset($kategoriToko) && $kategoriToko->count() > 0)
                    <div class="mb-4 mt-5">
                        <h4>Kategori dari Toko Mitra</h4>
                        <div class="row">
                            @foreach($kategoriToko as $kategori)
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="card h-100 kategori-card shadow-sm border-success">
                                    {{-- Gambar Kategori Toko - PERBAIKI PATH GAMBAR --}}
                                    <div class="position-relative">
                                        @if($kategori->gambar)
                                            <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                                                 alt="{{ $kategori->nama }}" 
                                                 class="card-img-top"
                                                 style="height: 200px; object-fit: cover;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            {{-- Fallback untuk gambar error --}}
                                            <div class="card-img-top bg-light align-items-center justify-content-center" 
                                                 style="height: 200px; display: none;">
                                                <i class="bi bi-shop text-success" style="font-size: 3rem;"></i>
                                            </div>
                                        @else
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                                 style="height: 200px;">
                                                <i class="bi bi-shop text-success" style="font-size: 3rem;"></i>
                                            </div>
                                        @endif
                                        
                                        {{-- Badge Toko --}}
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-success">{{ $kategori->toko->nama ?? 'Toko' }}</span>
                                        </div>
                                        
                                        {{-- Badge jenis kategori --}}
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-warning">{{ $kategori->getCategoryTypeLabel() }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body d-flex flex-column">
                                        {{-- Nama Kategori --}}
                                        <h6 class="card-title text-center">{{ $kategori->nama }}</h6>
                                        
                                        {{-- Harga --}}
                                        <div class="text-center mb-3">
                                            <span class="badge bg-success fs-6">
                                                Rp {{ number_format($kategori->harga, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        
                                        {{-- Deskripsi singkat --}}
                                        @if($kategori->deskripsi)
                                            <p class="card-text text-muted small text-center">
                                                {{ Str::limit($kategori->deskripsi, 50) }}
                                            </p>
                                        @endif
                                        
                                        {{-- Info Toko --}}
                                        <small class="text-center text-muted mb-2">
                                            Oleh: {{ $kategori->toko->nama ?? 'Toko Tidak Diketahui' }}
                                        </small>
                                        
                                        {{-- Tombol View dan Buy untuk kategori toko - DIPERBAIKI --}}
                                        <div class="mt-auto">
                                            <div class="d-grid gap-2">
                                                <button class="btn btn-outline-success btn-sm" 
                                                        onclick="viewKategoriToko({{ $kategori->id }})">
                                                    <i class="bi bi-eye"></i> View Toko
                                                </button>
                                                
                                                {{-- Tombol Buy kategori toko - DIPERBAIKI BISA DIBELI --}}
                                                @if(auth()->user()->role === 'pemilik_toko')
                                                    <form action="{{ route('pemilik-toko.buy.from.toko.kategori', $kategori->id) }}" method="POST" class="buy-form">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm w-100">
                                                            <i class="bi bi-cart-plus"></i> Beli dari Toko
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('customer.buy.from.toko.kategori', $kategori->id) }}" method="POST" class="buy-form">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm w-100">
                                                            <i class="bi bi-cart-plus"></i> Beli dari Toko
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                {{-- Tampilan jika tidak ada kategori --}}
                <div class="text-center py-5">
                    <i class="bi bi-heart-pulse" style="font-size: 5rem; color: #ccc;"></i>
                    <h4 class="mt-3">Kategori Belum Tersedia</h4>
                    <p class="text-muted">Silakan hubungi admin untuk menambahkan kategori produk.</p>
                </div>
            @endif
        </div>

        {{-- Sidebar Kategori (Kolom Kanan) dengan perbaikan scroll --}}
        <div class="col-md-3">
            {{-- Product Category Card dengan tinggi terbatas --}}
            <div class="card product-category-card">
                <div class="card-header">
                    <h5 class="mb-0">Product Category</h5>
                </div>
                <div class="card-body category-list-container">
                    <div class="list-group list-group-flush category-list">
                        {{-- Tampilkan Semua Kategori --}}
                        <div class="list-group-item list-group-item-action active">
                            <i class="bi bi-grid-3x3-gap me-2"></i>
                            Semua Kategori
                            <span class="badge bg-primary rounded-pill float-end">{{ $kategoris->count() }}</span>
                        </div>

                        {{-- Filter berdasarkan Jenis Kategori --}}
                        @php
                            $categoryTypes = $kategoris->groupBy('category_type');
                        @endphp
                        
                        @foreach($categoryTypes as $type => $kategorisByType)
                            <div class="list-group-item bg-light">
                                <small class="text-muted fw-bold">{{ ucwords(str_replace('-', ' ', $type)) }}</small>
                            </div>
                            @foreach($kategorisByType as $kategori)
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-chevron-right me-2"></i>
                                        {{ $kategori->nama }}
                                    </div>
                                    {{-- Tombol Buy Mini di Sidebar dengan route sesuai role --}}
                                    @if(auth()->user()->role === 'pemilik_toko')
                                        <form action="{{ route('pemilik-toko.buy.from.kategori', $kategori->id) }}" method="POST" class="d-inline buy-form-sidebar">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Buy {{ $kategori->nama }}">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('customer.buy.from.kategori', $kategori->id) }}" method="POST" class="d-inline buy-form-sidebar">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Buy {{ $kategori->nama }}">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Info Keranjang Quick Stats --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-cart3"></i> Status Keranjang
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        // Ambil data keranjang untuk menampilkan statistik
                        $keranjangItems = \App\Models\Keranjang::where('user_id', auth()->id())->get();
                        $totalItems = $keranjangItems->sum('jumlah');
                        $totalHarga = $keranjangItems->sum(function($item) {
                            return $item->jumlah * $item->harga;
                        });
                    @endphp
                    
                    @if($totalItems > 0)
                        <div class="text-center">
                            <h4 class="text-primary">{{ $totalItems }}</h4>
                            <p class="mb-1">Item dalam keranjang</p>
                            <h5 class="text-success">Rp {{ number_format($totalHarga, 0, ',', '.') }}</h5>
                            <small class="text-muted">Total sementara</small>
                            
                            <div class="d-grid mt-3">
                                @if(auth()->user()->role === 'pemilik_toko')
                                    <a href="{{ route('pemilik-toko.keranjang') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-cart-check"></i> Lihat Keranjang
                                    </a>
                                @else
                                    <a href="{{ route('customer.keranjang') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-cart-check"></i> Lihat Keranjang
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                            <p class="mb-0 mt-2">Keranjang kosong</p>
                            <small>Pilih kategori untuk berbelanja</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Section Feedback untuk Customer --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-chat-heart"></i> Feedback
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Berikan feedback pengalaman berbelanja Anda.</p>
                    @if(auth()->user()->role === 'pemilik_toko')
                        <form action="{{ route('pemilik-toko.feedback') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <textarea name="message" 
                                          class="form-control form-control-sm @error('message') is-invalid @enderror" 
                                          rows="3" 
                                          placeholder="Tulis feedback sebagai pemilik toko..."
                                          required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-send"></i> Kirim Feedback
                            </button>
                        </form>
                    @else
                        <form action="{{ route('customer.feedback') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <textarea name="message" 
                                          class="form-control form-control-sm @error('message') is-invalid @enderror" 
                                          rows="3" 
                                          placeholder="Tulis feedback Anda..."
                                          required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-send"></i> Kirim Feedback
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Info Status Toko jika customer --}}
            @if(auth()->user()->role === 'customer')
                <div class="card mt-3 mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-shop"></i> Status Toko Anda
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(auth()->user()->hasPendingTokoRequest())
                            <div class="text-center">
                                <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                                <p class="mb-1 mt-2">Permohonan Pending</p>
                                <small class="text-muted">Menunggu review admin</small>
                                <div class="d-grid mt-2">
                                    <a href="{{ route('customer.toko.status') }}" class="btn btn-warning btn-sm">
                                        Lihat Status
                                    </a>
                                </div>
                            </div>
                        @elseif(auth()->user()->hasApprovedToko())
                            <div class="text-center">
                                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                <p class="mb-1 mt-2">Toko Disetujui</p>
                                <small class="text-muted">Anda pemilik toko</small>
                                <div class="d-grid mt-2">
                                    <a href="{{ route('customer.toko.status') }}" class="btn btn-success btn-sm">
                                        Kelola Toko
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-center">
                                <i class="bi bi-shop text-info" style="font-size: 2rem;"></i>
                                <p class="mb-1 mt-2">Ingin Buka Toko?</p>
                                <small class="text-muted">Ajukan permohonan sekarang</small>
                                <div class="d-grid mt-2">
                                    <a href="{{ route('customer.toko.request') }}" class="btn btn-info btn-sm">
                                        Ajukan Toko
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif(auth()->user()->role === 'pemilik_toko')
                {{-- Info untuk pemilik toko --}}
                <div class="card mt-3 mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-shop-window"></i> Toko Anda
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <i class="bi bi-award text-success" style="font-size: 2rem;"></i>
                            <p class="mb-1 mt-2">Pemilik Toko Aktif</p>
                            <small class="text-muted">Kelola toko dan tetap bisa berbelanja</small>
                            <div class="d-grid gap-2 mt-2">
                                <a href="{{ route('pemilik-toko.dashboard') }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-speedometer2"></i> Dashboard Toko
                                </a>
                                <a href="{{ route('pemilik-toko.kategori') }}" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-tags"></i> Kelola Kategori
                                </a>
                                <a href="{{ route('pemilik-toko.shipping') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-truck"></i> Kelola Pengiriman
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal untuk View Detail Kategori --}}
<div class="modal fade" id="kategoriDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="kategoriDetailContent">
                {{-- Content akan diload via JavaScript --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="buyFromModal">
                    <i class="bi bi-cart-plus"></i> Beli Kategori Ini
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Custom CSS untuk perbaikan scroll dan layout --}}
@push('styles')
<style>
/* Perbaikan untuk Product Category Card */
.product-category-card {
    max-height: 450px;
    position: relative;
}

.category-list-container {
    max-height: 380px;
    overflow-y: auto;
    padding: 0;
}

.category-list {
    max-height: none;
    overflow: visible;
}

/* Custom scrollbar untuk category list */
.category-list-container::-webkit-scrollbar {
    width: 8px;
}

.category-list-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.category-list-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.category-list-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Firefox scrollbar */
.category-list-container {
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

/* Ensure proper spacing untuk cards */
.card {
    margin-bottom: 1rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Hover effects untuk kategori cards */
.kategori-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

/* Loading state untuk buy buttons */
.btn[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-category-card {
        max-height: 300px;
    }
    
    .category-list-container {
        max-height: 230px;
    }
    
    .kategori-card {
        margin-bottom: 1rem;
    }
    
    .d-flex.flex-wrap.gap-2 {
        gap: 0.5rem !important;
    }
    
    .d-flex.flex-wrap.gap-2 > * {
        margin-bottom: 0.5rem;
    }
}

/* List group items styling */
.list-group-item-action:hover {
    background-color: #f8f9fa;
}

.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
}

/* Badge positioning */
.float-end {
    float: right !important;
}

/* Form controls dalam sidebar */
.form-control-sm {
    font-size: 0.875rem;
}

/* Button styling */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Alert improvements */
.alert {
    margin-bottom: 1rem;
}

/* Card header styling */
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

/* Icon styling */
.bi {
    vertical-align: -0.125em;
}

/* Smooth transitions */
.card, .btn, .list-group-item {
    transition: all 0.2s ease-in-out;
}

/* Badge in navigation */
.badge {
    font-size: 0.75em;
}

/* Quick menu responsive */
@media (max-width: 576px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
}

/* Keranjang stats styling */
.text-center h4.text-primary {
    font-weight: bold;
    font-size: 2rem;
}

.text-center h5.text-success {
    font-weight: 600;
}

/* Status toko icon animations */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.bi-hourglass-split {
    animation: pulse 2s infinite;
}

/* Form submission loading state */
.loading {
    position: relative;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Role badge styling */
.badge.bg-success {
    font-size: 0.8em;
    padding: 0.4em 0.6em;
}

/* Gap utility for flexbox */
.gap-2 {
    gap: 0.5rem !important;
}

/* Grid gap for buttons */
.d-grid.gap-2 {
    gap: 0.5rem !important;
}

/* Fallback untuk gambar error */
.card-img-top + .card-img-top {
    display: none;
}
</style>
@endpush

@push('scripts')
<script>
// Variable untuk menyimpan kategori yang sedang dilihat
let currentKategoriId = null;

// Fungsi untuk view detail kategori (admin)
function viewKategori(id) {
    currentKategoriId = id;
    
    @foreach($kategoris as $kategori)
        if (id === {{ $kategori->id }}) {
            const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
            document.getElementById('kategoriDetailContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center mb-3">
                            @if($kategori->gambar)
                                <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                                     class="img-fluid rounded" 
                                     style="max-height: 250px;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                     style="height: 250px; display: none;">
                                    <i class="bi bi-heart-pulse text-primary" style="font-size: 4rem;"></i>
                                </div>
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                     style="height: 250px;">
                                    <i class="bi bi-heart-pulse text-primary" style="font-size: 4rem;"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>{{ $kategori->nama }}</h5>
                        <span class="badge bg-info mb-3">{{ $kategori->getCategoryTypeLabel() }}</span>
                        
                        <div class="mb-3">
                            <h6>Harga Kategori:</h6>
                            <h4 class="text-success">Rp {{ number_format($kategori->harga, 0, ',', '.') }}</h4>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Deskripsi:</h6>
                            <p class="text-muted">{{ $kategori->deskripsi ?: 'Tidak ada deskripsi' }}</p>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Info:</strong> Dengan membeli kategori ini, Anda akan mendapatkan produk sesuai kategori {{ $kategori->nama }} dengan harga yang tercantum.
                        </div>
                    </div>
                </div>
            `;
            
            // Update button untuk buy dari modal dengan route sesuai role
            const buyBtn = document.getElementById('buyFromModal');
            buyBtn.style.display = 'block';
            buyBtn.disabled = false;
            buyBtn.onclick = function() {
                // Submit form buy dari kategori dengan route sesuai role
                const form = document.createElement('form');
                form.method = 'POST';
                
                @if(auth()->user()->role === 'pemilik_toko')
                    form.action = '{{ route("pemilik-toko.buy.from.kategori", ":id") }}'.replace(':id', {{ $kategori->id }});
                @else
                    form.action = '{{ route("customer.buy.from.kategori", ":id") }}'.replace(':id', {{ $kategori->id }});
                @endif
                
                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = '{{ csrf_token() }}';
                form.appendChild(token);
                
                document.body.appendChild(form);
                
                // Add loading state
                buyBtn.disabled = true;
                buyBtn.classList.add('loading');
                buyBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
                
                form.submit();
            };
            
            modal.show();
            return;
        }
    @endforeach
}

// Fungsi untuk view detail kategori toko
function viewKategoriToko(id) {
    @if(isset($kategoriToko))
        @foreach($kategoriToko as $kategori)
            if (id === {{ $kategori->id }}) {
                const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
                document.getElementById('kategoriDetailContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center mb-3">
                                @if($kategori->gambar)
                                    <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                                         class="img-fluid rounded" 
                                         style="max-height: 250px;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                         style="height: 250px; display: none;">
                                        <i class="bi bi-shop text-success" style="font-size: 4rem;"></i>
                                    </div>
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                         style="height: 250px;">
                                        <i class="bi bi-shop text-success" style="font-size: 4rem;"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ $kategori->nama }}</h5>
                            <span class="badge bg-success mb-3">{{ $kategori->toko->nama ?? 'Toko' }}</span>
                            <span class="badge bg-warning mb-3">{{ $kategori->getCategoryTypeLabel() }}</span>
                            
                            <div class="mb-3">
                                <h6>Harga Kategori:</h6>
                                <h4 class="text-success">Rp {{ number_format($kategori->harga, 0, ',', '.') }}</h4>
                            </div>
                            
                            <div class="mb-3">
                                <h6>Deskripsi:</h6>
                                <p class="text-muted">{{ $kategori->deskripsi ?: 'Tidak ada deskripsi' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <h6>Toko:</h6>
                                <p class="text-muted">{{ $kategori->toko->nama ?? 'Toko Tidak Diketahui' }}</p>
                                @if($kategori->toko->no_telepon)
                                    <p class="text-muted">Kontak: {{ $kategori->toko->no_telepon }}</p>
                                @endif
                            </div>
                            
                            <div class="alert alert-success">
                                <i class="bi bi-info-circle"></i>
                                <strong>Info:</strong> Kategori ini disediakan oleh toko mitra. Klik tombol Beli untuk menambahkan ke keranjang.
                            </div>
                        </div>
                    </div>
                `;
                
                // Enable buy button for kategori toko
                const buyBtn = document.getElementById('buyFromModal');
                buyBtn.style.display = 'block';
                buyBtn.disabled = false;
                buyBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Beli dari Toko';
                buyBtn.onclick = function() {
                    // Submit form buy dari kategori toko dengan route sesuai role
                    const form = document.createElement('form');
                    form.method = 'POST';
                    
                    @if(auth()->user()->role === 'pemilik_toko')
                        form.action = '{{ route("pemilik-toko.buy.from.toko.kategori", ":id") }}'.replace(':id', {{ $kategori->id }});
                    @else
                        form.action = '{{ route("customer.buy.from.toko.kategori", ":id") }}'.replace(':id', {{ $kategori->id }});
                    @endif
                    
                    const token = document.createElement('input');
                    token.type = 'hidden';
                    token.name = '_token';
                    token.value = '{{ csrf_token() }}';
                    form.appendChild(token);
                    
                    document.body.appendChild(form);
                    
                    // Add loading state
                    buyBtn.disabled = true;
                    buyBtn.classList.add('loading');
                    buyBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
                    
                    form.submit();
                };
                
                modal.show();
                return;
            }
        @endforeach
    @endif
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });
    
    // Add loading state untuk semua tombol buy
    const buyForms = document.querySelectorAll('.buy-form, .buy-form-sidebar');
    buyForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                
                // Simpan text original
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
                
                // Reset jika ada error dalam 5 detik
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('loading');
                        submitBtn.innerHTML = originalText;
                    }
                }, 5000);
            }
        });
    });

    // Smooth scroll untuk category list
    const categoryContainer = document.querySelector('.category-list-container');
    if (categoryContainer) {
        categoryContainer.style.scrollBehavior = 'smooth';
    }

    // Auto refresh keranjang badge setiap 30 detik
    setInterval(function() {
        refreshKeranjangBadge();
    }, 30000);

    // Initialize tooltips jika ada
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Lazy loading untuk gambar kategori
    const images = document.querySelectorAll('.card-img-top[src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.style.opacity = '0';
                    img.onload = () => {
                        img.style.transition = 'opacity 0.3s';
                        img.style.opacity = '1';
                    };
                    observer.unobserve(img);
                }
            });
        });
        images.forEach(img => imageObserver.observe(img));
    }
});

// Function untuk refresh keranjang badge
function refreshKeranjangBadge() {
    // Tentukan route berdasarkan role user
    @if(auth()->user()->role === 'pemilik_toko')
        const keranjangRoute = '{{ route("pemilik-toko.keranjang") }}';
    @else
        const keranjangRoute = '{{ route("customer.keranjang") }}';
    @endif
    
    fetch(keranjangRoute, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        }
        throw new Error('Network response was not ok');
    })
    .then(data => {
        // Update badge jika ada data
        const badge = document.querySelector('.badge.bg-danger');
        const cartLinks = document.querySelectorAll('a[href*="keranjang"]');
        
        if (data.totalItems && data.totalItems > 0) {
            if (badge) {
                badge.textContent = data.totalItems;
            } else {
                // Create badge jika belum ada untuk semua cart links
                cartLinks.forEach(cartLink => {
                    if (!cartLink.querySelector('.badge')) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'badge bg-danger';
                        newBadge.textContent = data.totalItems;
                        cartLink.appendChild(document.createTextNode(' '));
                        cartLink.appendChild(newBadge);
                    }
                });
            }
        } else if (badge) {
            badge.remove();
        }
        
        // Update keranjang stats di sidebar jika ada
        updateKeranjangStats(data);
    })
    .catch(error => {
        console.log('Failed to refresh cart badge:', error);
    });
}

// Function untuk update keranjang stats di sidebar
function updateKeranjangStats(data = null) {
    if (!data) {
        // Refresh stats setelah buy
        setTimeout(() => {
            refreshKeranjangBadge();
        }, 1000);
        return;
    }
    
    // Update stats dengan data yang diberikan
    const statsContainer = document.querySelector('.card-body .text-center');
    if (statsContainer && data.totalItems !== undefined) {
        if (data.totalItems > 0) {
            statsContainer.innerHTML = `
                <h4 class="text-primary">${data.totalItems}</h4>
                <p class="mb-1">Item dalam keranjang</p>
                <h5 class="text-success">Rp ${new Intl.NumberFormat('id-ID').format(data.totalHarga || 0)}</h5>
                <small class="text-muted">Total sementara</small>
                <div class="d-grid mt-3">
                    @if(auth()->user()->role === 'pemilik_toko')
                        <a href="{{ route('pemilik-toko.keranjang') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-cart-check"></i> Lihat Keranjang
                        </a>
                    @else
                        <a href="{{ route('customer.keranjang') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-cart-check"></i> Lihat Keranjang
                        </a>
                    @endif
                </div>
            `;
        } else {
            statsContainer.innerHTML = `
                <div class="text-center text-muted">
                    <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                    <p class="mb-0 mt-2">Keranjang kosong</p>
                    <small>Pilih kategori untuk berbelanja</small>
                </div>
            `;
        }
    }
}

// Function untuk handle success buy
function handleBuySuccess() {
    // Show success message
    const alertContainer = document.querySelector('.container-fluid .row .col-12');
    if (alertContainer) {
        const successAlert = document.createElement('div');
        successAlert.className = 'alert alert-success alert-dismissible fade show';
        successAlert.innerHTML = `
            Produk berhasil ditambahkan ke keranjang!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(successAlert);
        
        // Auto hide after 3 seconds
        setTimeout(() => {
            successAlert.remove();
        }, 3000);
    }
    
    // Update keranjang stats
    updateKeranjangStats();
}

// Function untuk handle buy error
function handleBuyError(message = 'Terjadi kesalahan saat menambahkan ke keranjang') {
    // Show error message
    const alertContainer = document.querySelector('.container-fluid .row .col-12');
    if (alertContainer) {
        const errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
        errorAlert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(errorAlert);
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            errorAlert.remove();
        }, 5000);
    }
}

// Utility function untuk format rupiah
function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Add keyboard navigation untuk kategori cards
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Close modal if open
        const modal = bootstrap.Modal.getInstance(document.getElementById('kategoriDetailModal'));
        if (modal) {
            modal.hide();
        }
    }
});

// Performance optimization - debounce scroll events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Optimized scroll handler untuk sidebar
const categoryContainer = document.querySelector('.category-list-container');
if (categoryContainer) {
    const handleScroll = debounce(() => {
        // Handle scroll events dengan debounce untuk performance
        const scrollTop = categoryContainer.scrollTop;
        const scrollHeight = categoryContainer.scrollHeight;
        const clientHeight = categoryContainer.clientHeight;
        
        // Add shadow effect when scrolling
        if (scrollTop > 0) {
            categoryContainer.style.boxShadow = 'inset 0 10px 10px -10px rgba(0,0,0,0.1)';
        } else {
            categoryContainer.style.boxShadow = 'none';
        }
        
        if (scrollTop + clientHeight >= scrollHeight - 5) {
            categoryContainer.style.boxShadow += ', inset 0 -10px 10px -10px rgba(0,0,0,0.1)';
        }
    }, 16); // ~60fps
    
    categoryContainer.addEventListener('scroll', handleScroll);
}
</script>
@endpush