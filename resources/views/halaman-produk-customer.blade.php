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

    {{-- Quick Menu untuk Customer - DIPERBAIKI --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-0">Menu Customer</h5>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ 
                        auth()->user()->role === 'pemilik_toko' 
                            ? route('pemilik-toko.keranjang') 
                            : route('customer.keranjang') 
                    }}" class="btn btn-primary">
                        <i class="bi bi-cart3"></i> Keranjang Belanja
                        @php
                            $jumlahKeranjang = \App\Models\Keranjang::where('user_id', auth()->id())->sum('jumlah');
                        @endphp
                        @if($jumlahKeranjang > 0)
                            <span class="badge bg-danger">{{ $jumlahKeranjang }}</span>
                        @endif
                    </a>
                    <a href="{{ 
                        auth()->user()->role === 'pemilik_toko' 
                            ? route('pemilik-toko.order.history') 
                            : route('customer.order.history') 
                    }}" class="btn btn-secondary">
                        <i class="bi bi-clock-history"></i> Riwayat Pesanan
                    </a>
                    @if(auth()->user()->role === 'customer')
                        @if(!auth()->user()->hasPendingTokoRequest() && !auth()->user()->hasApprovedToko())
                            <a href="{{ route('customer.toko.request') }}" class="btn btn-warning">
                                <i class="bi bi-shop"></i> Ajukan Toko
                            </a>
                        @else
                            <a href="{{ route('customer.toko.status') }}" class="btn btn-info">
                                <i class="bi bi-shop"></i> Status Toko
                            </a>
                        @endif
                    @elseif(auth()->user()->role === 'pemilik_toko')
                        <a href="{{ route('pemilik-toko.dashboard') }}" class="btn btn-success">
                            <i class="bi bi-shop"></i> Dashboard Toko
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content dengan Layout seperti SRS --}}
    <div class="row">
        {{-- Product Display Area (Kolom Kiri) - Kategori Produk dan Toko Mitra --}}
        <div class="col-md-9">
            @if($kategoris->count() > 0)
                <div class="mb-4">
                    <h3>Kategori Produk</h3>
                    <div class="row">
                        @foreach($kategoris as $kategori)
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="card h-100 kategori-card shadow-sm">
                                <div class="position-relative" style="height: 200px; overflow: hidden;">
                                    @if($kategori->gambar && file_exists(storage_path('app/public/' . $kategori->gambar)))
                                        <img src="{{ asset('storage/' . $kategori->gambar) }}"
                                             alt="{{ $kategori->nama }}"
                                             class="card-img-top"
                                             style="width: 100%; height: 200px; object-fit: cover;">
                                    @else
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                             style="height: 200px;">
                                            <div class="text-center">
                                                <i class="bi bi-heart-pulse text-primary" style="font-size: 3rem;"></i>
                                                <br><small class="text-muted">Belum Ada Gambar</small>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-info">{{ $kategori->getCategoryTypeLabel() }}</span>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title text-center">{{ $kategori->nama }}</h6>
                                    <div class="text-center mb-3">
                                        <span class="badge bg-success fs-6">
                                            Rp {{ number_format($kategori->harga, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    @if($kategori->deskripsi)
                                        <p class="card-text text-muted small text-center">
                                            {{ Str::limit($kategori->deskripsi, 50) }}
                                        </p>
                                    @endif
                                    <div class="mt-auto">
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-outline-primary btn-sm"
                                                    onclick="viewKategori({{ $kategori->id }})">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            {{-- Perbaikan form buy berdasarkan role --}}
                                            @if(auth()->user()->role === 'pemilik_toko')
                                                <form action="{{ route('pemilik-toko.buy.from.kategori', $kategori->id) }}" 
                                                      method="POST" 
                                                      style="display: inline;" 
                                                      class="buy-form">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                                        <i class="bi bi-bag-plus"></i> Buy
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('customer.buy.from.kategori', $kategori->id) }}" 
                                                      method="POST" 
                                                      style="display: inline;" 
                                                      class="buy-form">
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

                {{-- Kategori dari Toko-toko untuk Customer tanpa lazy loading --}}
                @if(isset($kategoriToko) && $kategoriToko->count() > 0)
                    <div class="mb-4 mt-5">
                        <h4>Kategori dari Toko Mitra</h4>
                        <div class="row">
                            @foreach($kategoriToko as $kategori)
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="card h-100 kategori-card shadow-sm border-success">
                                    <div class="position-relative">
                                        @if($kategori->gambar && file_exists(storage_path('app/public/' . $kategori->gambar)))
                                            <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                                                 alt="{{ $kategori->nama }}" 
                                                 class="card-img-top"
                                                 style="height: 200px; object-fit: cover; width: 100%;">
                                        @else
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                                 style="height: 200px;">
                                                <div class="text-center">
                                                    <i class="bi bi-shop text-success" style="font-size: 3rem;"></i>
                                                    <br><small class="text-muted">Belum Ada Gambar</small>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-success">{{ $kategori->toko->nama ?? 'Toko' }}</span>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-warning">{{ $kategori->getCategoryTypeLabel() }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title text-center">{{ $kategori->nama }}</h6>
                                        <div class="text-center mb-3">
                                            <span class="badge bg-success fs-6">
                                                Rp {{ number_format($kategori->harga, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        @if($kategori->deskripsi)
                                            <p class="card-text text-muted small text-center">
                                                {{ Str::limit($kategori->deskripsi, 50) }}
                                            </p>
                                        @endif
                                        <small class="text-center text-muted mb-2">
                                            Oleh: {{ $kategori->toko->nama ?? 'Toko Tidak Diketahui' }}
                                        </small>
                                        <div class="mt-auto">
                                            <div class="d-grid gap-2">
                                                <button class="btn btn-outline-success btn-sm"
                                                        onclick="viewKategoriToko({{ $kategori->id }})">
                                                    <i class="bi bi-eye"></i> View Toko
                                                </button>
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
                @else
                    <div class="alert alert-info">
                        <strong>Info:</strong> Tidak ada kategori toko mitra yang tersedia atau belum ada toko yang approved.
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bi bi-heart-pulse" style="font-size: 5rem; color: #ccc;"></i>
                    <h4 class="mt-3">Kategori Belum Tersedia</h4>
                    <p class="text-muted">Silakan hubungi admin untuk menambahkan kategori produk.</p>
                </div>
            @endif
        </div>

        {{-- Sidebar Kategori (Kolom Kanan) dengan perbaikan scroll --}}
        <div class="col-md-3">
            <div class="card product-category-card">
                <div class="card-header">
                    <h5 class="mb-0">Product Category</h5>
                </div>
                <div class="card-body category-list-container">
                    <div class="list-group list-group-flush category-list">
                        <div class="list-group-item list-group-item-action active">
                            <i class="bi bi-grid-3x3-gap me-2"></i>
                            Semua Kategori
                            <span class="badge bg-primary rounded-pill float-end">{{ $kategoris->count() }}</span>
                        </div>
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
                                    {{-- Perbaikan form buy di sidebar --}}
                                    @if(auth()->user()->role === 'pemilik_toko')
                                        <form action="{{ route('pemilik-toko.buy.from.kategori', $kategori->id) }}" 
                                              method="POST" 
                                              class="d-inline buy-form-sidebar">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" 
                                                    title="Buy {{ $kategori->nama }}">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('customer.buy.from.kategori', $kategori->id) }}" 
                                              method="POST" 
                                              class="d-inline buy-form-sidebar">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" 
                                                    title="Buy {{ $kategori->nama }}">
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

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-cart3"></i> Status Keranjang
                    </h6>
                </div>
                <div class="card-body">
                    @php
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

<div class="modal fade" id="kategoriDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="kategoriDetailContent">
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

/* Prevent layout shift */
.kategori-card {
    min-height: 400px;
}

/* Button styling */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
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
</style>
@endpush

@push('scripts')
<script>
let currentKategoriId = null;

function viewKategori(id) {
    currentKategoriId = id;
    @foreach($kategoris as $kategori)
        if (id === {{ $kategori->id }}) {
            const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
            document.getElementById('kategoriDetailContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center mb-3">
                            @if($kategori->gambar && file_exists(storage_path('app/public/' . $kategori->gambar)))
                                <img src="{{ asset('storage/' . $kategori->gambar) }}"
                                     class="img-fluid rounded"
                                     style="max-height: 250px;"
                                     alt="{{ $kategori->nama }}">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded"
                                     style="height: 250px;">
                                    <div class="text-center">
                                        <i class="bi bi-heart-pulse text-primary" style="font-size: 4rem;"></i>
                                        <br><small class="text-muted">Belum Ada Gambar</small>
                                    </div>
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
            const buyBtn = document.getElementById('buyFromModal');
            buyBtn.style.display = 'block';
            buyBtn.disabled = false;
            buyBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Beli Kategori Ini';
            buyBtn.onclick = function() {
                const form = document.createElement('form');
                form.method = 'POST';
                // Perbaikan route berdasarkan role user
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
                buyBtn.disabled = true;
                buyBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
                form.submit();
            };
            modal.show();
            return;
        }
    @endforeach
}

function viewKategoriToko(id) {
    @if(isset($kategoriToko))
        @foreach($kategoriToko as $kategori)
            if (id === {{ $kategori->id }}) {
                const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
                document.getElementById('kategoriDetailContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center mb-3">
                                @if($kategori->gambar && file_exists(storage_path('app/public/' . $kategori->gambar)))
                                    <img src="{{ asset('storage/' . $kategori->gambar) }}"
                                         class="img-fluid rounded"
                                         style="max-height: 250px;"
                                         alt="{{ $kategori->nama }}">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded"
                                         style="height: 250px;">
                                        <div class="text-center">
                                            <i class="bi bi-shop text-success" style="font-size: 4rem;"></i>
                                            <br><small class="text-muted">Belum Ada Gambar</small>
                                        </div>
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
                const buyBtn = document.getElementById('buyFromModal');
                buyBtn.style.display = 'block';
                buyBtn.disabled = false;
                buyBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Beli dari Toko';
                buyBtn.onclick = function() {
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
                    buyBtn.disabled = true;
                    buyBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
                    form.submit();
                };
                modal.show();
                return;
            }
        @endforeach
    @endif
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });

    // Buy form handling
    const buyForms = document.querySelectorAll('.buy-form, .buy-form-sidebar');
    buyForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Buy';
                    }
                }, 5000);
            }
        });
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush