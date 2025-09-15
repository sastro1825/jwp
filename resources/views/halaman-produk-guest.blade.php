@extends('layouts.app')

@section('content')
{{-- Halaman Produk untuk Visitor/Guest sesuai SRS --}}
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Product Page - Toko Alat Kesehatan</h1>
            <p class="text-muted">Selamat datang di OSS! Silakan <a href="{{ route('register') }}" class="text-primary">daftar</a> atau <a href="{{ route('login') }}" class="text-primary">login</a> untuk mulai berbelanja.</p>
        </div>
    </div>

    {{-- Main Content dengan Layout seperti SRS --}}
    <div class="row">
        {{-- Product Display Area (Kolom Kiri) - HANYA Menampilkan Kategori Produk --}}
        <div class="col-md-9">
            {{-- Tampilkan Kategori Produk dengan Gambar, Harga, View, Buy untuk Guest --}}
            @if($kategoris->count() > 0)
                <div class="mb-4">
                    <h3>Kategori Produk</h3>
                    <div class="row">
                        @foreach($kategoris as $kategori)
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="card h-100 kategori-card shadow-sm">
                                {{-- Gambar Kategori dengan ukuran tetap --}}
                                <div class="position-relative">
                                    @if($kategori->gambar)
                                        <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                                             alt="{{ $kategori->nama }}" 
                                             class="card-img-top"
                                             style="height: 200px; object-fit: cover;">
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
                                    
                                    {{-- Tombol View dan Buy sesuai SRS untuk Guest --}}
                                    <div class="mt-auto">
                                        <div class="d-grid gap-2">
                                            {{-- Tombol View untuk melihat detail kategori --}}
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    onclick="viewKategori({{ $kategori->id }})">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            
                                            {{-- Tombol Buy mengarah ke login --}}
                                            <a href="{{ route('login') }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-bag-plus"></i> Buy (Login Required)
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Tampilan jika tidak ada kategori --}}
                <div class="text-center py-5">
                    <i class="bi bi-heart-pulse" style="font-size: 5rem; color: #ccc;"></i>
                    <h4 class="mt-3">Kategori Belum Tersedia</h4>
                    <p class="text-muted">Silakan hubungi admin untuk menambahkan kategori produk.</p>
                </div>
            @endif
        </div>

        {{-- Sidebar Kategori (Kolom Kanan) sesuai SRS --}}
        <div class="col-md-3">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5 class="mb-0">Product Category</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
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
                                <div class="list-group-item list-group-item-action">
                                    <i class="bi bi-chevron-right me-2"></i>
                                    {{ $kategori->nama }}
                                    <span class="badge bg-secondary rounded-pill float-end">{{ $kategori->produks->count() }}</span>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Call to Action untuk Guest --}}
            <div class="card mt-3">
                <div class="card-body text-center">
                    <i class="bi bi-person-plus" style="font-size: 3rem; color: #007bff;"></i>
                    <h6 class="mt-2">Bergabung dengan OSS</h6>
                    <p class="small text-muted">Daftar sekarang untuk melihat produk dan berbelanja!</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-person-plus-fill"></i> Daftar Gratis
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </div>
                </div>
            </div>

            {{-- Info OSS untuk Guest --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Tentang OSS
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">OSS - Online Shopping System adalah toko alat kesehatan terpercaya yang menyediakan berbagai produk kesehatan berkualitas.</p>
                    <ul class="small text-muted">
                        <li>Produk kesehatan original</li>
                        <li>Pembayaran aman (prepaid/postpaid)</li>
                        <li>Pengiriman cepat</li>
                        <li>Customer service 24/7</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal untuk View Detail Kategori untuk Guest --}}
<div class="modal fade" id="kategoriDetailModal" tabindex="-1">
    <div class="modal-dialog">
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
                <a href="{{ route('login') }}" class="btn btn-primary">Login untuk Berbelanja</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Fungsi untuk view detail kategori untuk guest
function viewKategori(id) {
    // Find kategori data dari server-side data
    @foreach($kategoris as $kategori)
        if (id === {{ $kategori->id }}) {
            const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
            document.getElementById('kategoriDetailContent').innerHTML = `
                <div class="text-center mb-3">
                    @if($kategori->gambar)
                        <img src="{{ asset('storage/' . $kategori->gambar) }}" class="img-fluid mb-3" style="max-height: 200px;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center mb-3" style="height: 200px;">
                            <i class="bi bi-heart-pulse text-primary" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                    <h5>{{ $kategori->nama }}</h5>
                    <span class="badge bg-info mb-2">{{ $kategori->getCategoryTypeLabel() }}</span>
                </div>
                <div class="alert alert-info">
                    <h6>Deskripsi:</h6>
                    <p class="mb-2">{{ $kategori->deskripsi }}</p>
                    <p class="mb-0"><strong>Harga Mulai:</strong> Rp {{ number_format($kategori->harga, 0, ',', '.') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-muted">
                        <i class="bi bi-box"></i>
                        Tersedia {{ $kategori->produks->count() }} produk dalam kategori ini
                    </p>
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i>
                        Login untuk melihat produk dalam kategori ini dan melakukan pembelian.
                    </div>
                </div>
            `;
            modal.show();
            return;
        }
    @endforeach
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
});
</script>
@endpush