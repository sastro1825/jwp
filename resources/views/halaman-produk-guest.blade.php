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
        {{-- Product Display Area (Kolom Kiri) --}}
        <div class="col-md-9">
            {{-- Tampilkan Kategori Produk --}}
            @if($kategoris->count() > 0)
                <div class="mb-4">
                    <h3>Kategori Produk</h3>
                    <div class="row">
                        @foreach($kategoris as $kategori)
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="card h-100 kategori-card">
                                {{-- Gambar Kategori --}}
                                <div class="card-body text-center">
                                    @if($kategori->gambar)
                                        <img src="{{ Storage::url($kategori->gambar) }}" 
                                             alt="{{ $kategori->nama }}" 
                                             class="img-fluid mb-2"
                                             style="max-height: 80px; object-fit: contain;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center mb-2" 
                                             style="height: 80px; border-radius: 5px;">
                                            <i class="bi bi-heart-pulse text-primary" style="font-size: 2.5rem;"></i>
                                        </div>
                                    @endif
                                    
                                    <h6 class="card-title">{{ $kategori->nama }}</h6>
                                    <span class="badge bg-success mb-2">
                                        Rp {{ number_format($kategori->harga, 0, ',', '.') }}
                                    </span>
                                    
                                    {{-- Tombol View dan Buy sesuai SRS --}}
                                    <div class="d-grid gap-1">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewKategori({{ $kategori->id }})">
                                            View
                                        </button>
                                        <a href="{{ route('login') }}" class="btn btn-sm btn-primary">
                                            Buy (Login Required)
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Area Produk berdasarkan Kategori yang dipilih --}}
            <div id="produkArea">
                @if($produks->count() > 0)
                    <div class="mb-4">
                        <h4>Produk Tersedia</h4>
                        <div class="row" id="produkContainer">
                            @foreach($produks as $produk)
                            <div class="col-md-4 mb-3">
                                <div class="card produk-card h-100">
                                    {{-- Badge Kategori --}}
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-primary">{{ $produk->kategori->nama }}</span>
                                    </div>
                                    
                                    {{-- Gambar Produk (placeholder) --}}
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                        <i class="bi bi-box text-muted" style="font-size: 3rem;"></i>
                                    </div>

                                    <div class="card-body d-flex flex-column">
                                        {{-- Nama Produk --}}
                                        <h6 class="card-title">{{ $produk->nama }}</h6>
                                        <p class="card-text text-muted small">ID: {{ $produk->id_produk }}</p>
                                        
                                        {{-- Harga tersembunyi untuk guest --}}
                                        <div class="mb-2">
                                            <div class="bg-light p-2 text-center rounded">
                                                <i class="bi bi-eye-slash text-muted"></i>
                                                <small class="text-muted d-block">Login untuk melihat harga</small>
                                            </div>
                                        </div>
                                        
                                        {{-- Tombol View dan Buy sesuai SRS --}}
                                        <div class="mt-auto">
                                            <div class="d-grid gap-1">
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewProduk({{ $produk->id }})">
                                                    View
                                                </button>
                                                <a href="{{ route('login') }}" class="btn btn-sm btn-primary">
                                                    Buy
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-center">
                            {{ $produks->links() }}
                        </div>
                    </div>
                @else
                    {{-- Tampilan jika tidak ada produk --}}
                    <div class="text-center py-5">
                        <i class="bi bi-box" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3">Pilih Kategori</h4>
                        <p class="text-muted">Pilih kategori di atas untuk melihat produk yang tersedia.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar Kategori (Kolom Kanan) sesuai SRS --}}
        <div class="col-md-3">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5 class="mb-0">Product Category</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        {{-- Tampilkan Semua Produk --}}
                        <a href="{{ route('home') }}" 
                           class="list-group-item list-group-item-action {{ !request()->has('kategori_id') ? 'active' : '' }}">
                            <i class="bi bi-grid-3x3-gap me-2"></i>
                            Semua Produk
                            <span class="badge bg-primary rounded-pill float-end">{{ $kategoris->sum('produks_count') ?? 'All' }}</span>
                        </a>

                        {{-- Filter berdasarkan Jenis Kategori --}}
                        @php
                            $categoryTypes = $kategoris->groupBy('category_type');
                        @endphp
                        
                        @foreach($categoryTypes as $type => $kategorisByType)
                            <div class="list-group-item bg-light">
                                <small class="text-muted fw-bold">{{ ucwords(str_replace('-', ' ', $type)) }}</small>
                            </div>
                            @foreach($kategorisByType as $kategori)
                                <a href="{{ route('home', ['kategori_id' => $kategori->id]) }}" 
                                   class="list-group-item list-group-item-action {{ request('kategori_id') == $kategori->id ? 'active' : '' }}">
                                    <i class="bi bi-chevron-right me-2"></i>
                                    {{ $kategori->nama }}
                                    <span class="badge bg-secondary rounded-pill float-end">{{ $kategori->produks->count() }}</span>
                                </a>
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
                    <p class="small text-muted">Daftar sekarang untuk melihat harga dan berbelanja!</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                            Daftar Gratis
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                            Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal untuk View Detail Produk --}}
<div class="modal fade" id="produkDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="produkDetailContent">
                {{-- Content akan diload via AJAX --}}
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="{{ route('login') }}" class="btn btn-primary">Login untuk Beli</a>
            </div>
        </div>
    </div>
</div>

{{-- Modal untuk View Detail Kategori --}}
<div class="modal fade" id="kategoriDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="kategoriDetailContent">
                {{-- Content akan diload via AJAX --}}
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
// Fungsi untuk view detail produk via AJAX
function viewProduk(id) {
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('produkDetailModal'));
    modal.show();
    
    // Load content via AJAX
    fetch(`/produk/view/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('produkDetailContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                            ${data.gambar ? `<img src="${data.gambar}" class="img-fluid" style="max-height: 200px;">` : '<i class="bi bi-image" style="font-size: 4rem; color: #ccc;"></i>'}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>${data.nama}</h5>
                        <p class="text-muted">ID: ${data.id_produk}</p>
                        <div class="mb-3">
                            <div class="bg-light p-3 text-center rounded">
                                <i class="bi bi-eye-slash text-muted"></i>
                                <p class="text-muted mb-0">Login untuk melihat harga</p>
                            </div>
                        </div>
                        <p><strong>Kategori:</strong> ${data.kategori}</p>
                        <p><strong>Toko:</strong> ${data.toko}</p>
                        <div class="alert alert-info">
                            <h6>Deskripsi:</h6>
                            <p class="mb-0">${data.deskripsi}</p>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('produkDetailContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Gagal memuat detail produk. Silakan coba lagi.
                </div>
            `;
        });
}

// Fungsi untuk view detail kategori
function viewKategori(id) {
    // Find kategori data
    @foreach($kategoris as $kategori)
        if (id === {{ $kategori->id }}) {
            const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
            document.getElementById('kategoriDetailContent').innerHTML = `
                <div class="text-center mb-3">
                    @if($kategori->gambar)
                        <img src="{{ Storage::url($kategori->gambar) }}" class="img-fluid mb-3" style="max-height: 150px;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center mb-3" style="height: 150px;">
                            <i class="bi bi-heart-pulse text-primary" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                    <h5>{{ $kategori->nama }}</h5>
                    <span class="badge bg-info mb-2">{{ $kategori->getCategoryTypeLabel() }}</span>
                </div>
                <div class="alert alert-info">
                    <h6>Deskripsi:</h6>
                    <p class="mb-2">{{ $kategori->deskripsi }}</p>
                    <p class="mb-0"><strong>Harga Patokan:</strong> Rp {{ number_format($kategori->harga, 0, ',', '.') }}</p>
                </div>
                <p class="text-muted text-center">
                    <i class="bi bi-info-circle"></i>
                    Login untuk melihat produk dalam kategori ini dan melakukan pembelian.
                </p>
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