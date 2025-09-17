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

    {{-- Alert Messages untuk feedback --}}
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

    {{-- Main Content dengan Layout seperti SRS --}}
    <div class="row">
        {{-- Product Display Area (Kolom Kiri) - Kategori Admin + Kategori Toko --}}
        <div class="col-md-9">
            {{-- Tampilkan Kategori Produk Admin --}}
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
                                             style="height: 200px; object-fit: cover;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        {{-- Fallback untuk gambar error --}}
                                        <div class="card-img-top bg-light align-items-center justify-content-center" 
                                             style="height: 200px; display: none;">
                                            <i class="bi bi-heart-pulse text-primary" style="font-size: 3rem;"></i>
                                        </div>
                                    @else
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
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    onclick="viewKategori({{ $kategori->id }})">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            
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
            @endif
            
            {{-- Kategori dari Toko-toko untuk Guest - DENGAN DEBUGGING DIHAPUS --}}
            @if(isset($kategoriToko) && $kategoriToko->count() > 0)
                <div class="mb-4 mt-5">
                    <h4>Kategori dari Toko Mitra</h4>
                    <div class="row">
                        @foreach($kategoriToko as $kategori)
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="card h-100 kategori-card shadow-sm border-success">
                                {{-- Gambar Kategori Toko --}}
                                <div class="position-relative">
                                    @php
                                        // Periksa apakah file gambar ada
                                        $imagePath = $kategori->gambar ? public_path('storage/' . $kategori->gambar) : null;
                                        $imageExists = $imagePath ? file_exists($imagePath) : false;
                                    @endphp
                                    
                                    @if($kategori->gambar && $imageExists)
                                        {{-- Gambar ada dan dapat diakses --}}
                                        <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                                             alt="{{ $kategori->nama }}" 
                                             class="card-img-top"
                                             style="height: 200px; object-fit: cover;"
                                             onload="console.log('Image loaded: {{ $kategori->nama }}');"
                                             onerror="console.log('Image failed: {{ $kategori->gambar }}'); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        {{-- Fallback jika gambar gagal load --}}
                                        <div class="card-img-top bg-light align-items-center justify-content-center" 
                                             style="height: 200px; display: none;">
                                            <i class="bi bi-shop text-success" style="font-size: 3rem;"></i>
                                            <br><small class="text-muted">Gambar Tidak Tersedia</small>
                                        </div>
                                    @else
                                        {{-- Placeholder default --}}
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                             style="height: 200px;">
                                            <div class="text-center">
                                                <i class="bi bi-shop text-success" style="font-size: 3rem;"></i>
                                                <br><small class="text-muted">
                                                    @if($kategori->gambar)
                                                        Gambar Tidak Ditemukan
                                                    @else
                                                        Belum Ada Gambar
                                                    @endif
                                                </small>
                                            </div>
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
                                
                                {{-- Card body content tetap sama --}}
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
                                                    onclick="viewKategoriTokoGuest({{ $kategori->id }})">
                                                <i class="bi bi-eye"></i> View Toko
                                            </button>
                                            <a href="{{ route('login') }}" class="btn btn-success btn-sm">
                                                <i class="bi bi-bag-plus"></i> Login untuk Beli
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Tampilan jika tidak ada kategori sama sekali --}}
            @if($kategoris->count() == 0 && (!isset($kategoriToko) || $kategoriToko->count() == 0))
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

            {{-- Section Feedback untuk Visitor --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-chat-heart"></i> Berikan Feedback
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Beri tahu kami pendapat Anda tentang OSS.</p>
                    <form action="{{ route('guest.feedback') }}" method="POST">
                        @csrf
                        {{-- Nama Visitor --}}
                        <div class="mb-2">
                            <input type="text" 
                                   name="name" 
                                   class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                   placeholder="Nama Anda"
                                   value="{{ old('name') }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Email Visitor --}}
                        <div class="mb-2">
                            <input type="email" 
                                   name="email" 
                                   class="form-control form-control-sm @error('email') is-invalid @enderror" 
                                   placeholder="Email Anda"
                                   value="{{ old('email') }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Pesan Feedback --}}
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
                </div>
            </div>

            {{-- Info OSS untuk Guest --}}
            <div class="card mt-3 mb-4">
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
                <a href="{{ route('login') }}" class="btn btn-primary">Login untuk Berbelanja</a>
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-category-card {
        max-height: 300px;
    }
    
    .category-list-container {
        max-height: 230px;
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

/* Fallback untuk gambar error */
.card-img-top + .card-img-top {
    display: none;
}
</style>
@endpush

@push('scripts')
<script>
// Fungsi untuk view detail kategori admin
function viewKategori(id) {
    @foreach($kategoris as $kategori)
        if (id === {{ $kategori->id }}) {
            const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
            document.getElementById('kategoriDetailContent').innerHTML = `
                <div class="text-center mb-3">
                    @if($kategori->gambar)
                        <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                             class="img-fluid mb-3" 
                             style="max-height: 200px;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="bg-light d-flex align-items-center justify-content-center mb-3" 
                             style="height: 200px; display: none;">
                            <i class="bi bi-heart-pulse text-primary" style="font-size: 4rem;"></i>
                        </div>
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center mb-3" 
                             style="height: 200px;">
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

// Fungsi untuk view detail kategori toko untuk guest
function viewKategoriTokoGuest(id) {
    @if(isset($kategoriToko))
        @foreach($kategoriToko as $kategori)
            if (id === {{ $kategori->id }}) {
                const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
                document.getElementById('kategoriDetailContent').innerHTML = `
                    <div class="text-center mb-3">
                        @if($kategori->gambar)
                            <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                                 class="img-fluid mb-3" 
                                 style="max-height: 200px;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="bg-light d-flex align-items-center justify-content-center mb-3" 
                                 style="height: 200px; display: none;">
                                <i class="bi bi-shop text-success" style="font-size: 4rem;"></i>
                            </div>
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center mb-3" 
                                 style="height: 200px;">
                                <i class="bi bi-shop text-success" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                        <h5>{{ $kategori->nama }}</h5>
                        <span class="badge bg-success mb-2">{{ $kategori->toko->nama ?? 'Toko' }}</span>
                        <span class="badge bg-warning mb-2">{{ $kategori->getCategoryTypeLabel() }}</span>
                    </div>
                    <div class="alert alert-info">
                        <h6>Deskripsi:</h6>
                        <p class="mb-2">{{ $kategori->deskripsi ?: 'Tidak ada deskripsi' }}</p>
                        <p class="mb-0"><strong>Harga:</strong> Rp {{ number_format($kategori->harga, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-muted">
                            <i class="bi bi-shop"></i>
                            Produk dari toko mitra: {{ $kategori->toko->nama ?? 'Toko Tidak Diketahui' }}
                        </p>
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i>
                            Login untuk melihat detail lengkap dan melakukan pembelian dari toko mitra.
                        </div>
                    </div>
                `;
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

    // Smooth scroll untuk category list
    const categoryContainer = document.querySelector('.category-list-container');
    if (categoryContainer) {
        categoryContainer.style.scrollBehavior = 'smooth';
    }

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
</script>
@endpush