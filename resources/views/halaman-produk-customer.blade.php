@extends('layouts.app')

@section('content')
{{-- Halaman Produk untuk Customer yang sudah login sesuai SRS --}}
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Product Page - Toko Alat Kesehatan</h1>
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

    {{-- Quick Menu untuk Customer --}}
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
                                        Mulai Rp {{ number_format($kategori->harga, 0, ',', '.') }}
                                    </span>
                                    
                                    {{-- Tombol View dan Buy sesuai SRS --}}
                                    <div class="d-grid gap-1">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewKategori({{ $kategori->id }})">
                                            View
                                        </button>
                                        <a href="{{ route('home', ['kategori_id' => $kategori->id]) }}" class="btn btn-sm btn-primary">
                                            Buy
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
                        <h4>Produk Tersedia 
                            @if(request()->has('kategori_id'))
                                @php
                                    $selectedKategori = $kategoris->where('id', request('kategori_id'))->first();
                                @endphp
                                @if($selectedKategori)
                                    <small class="text-muted">(Kategori: {{ $selectedKategori->nama }})</small>
                                @endif
                            @endif
                        </h4>
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
                                        {{-- Nama Produk dan Harga --}}
                                        <h6 class="card-title">{{ $produk->nama }}</h6>
                                        <p class="card-text text-muted small">ID: {{ $produk->id_produk }}</p>
                                        
                                        {{-- Harga Produk --}}
                                        <h5 class="text-success mb-2">
                                            Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                        </h5>
                                        
                                        {{-- Info Toko --}}
                                        <small class="text-muted mb-2">
                                            <i class="bi bi-shop"></i> {{ $produk->toko->nama ?? 'Toko Resmi' }}
                                        </small>
                                        
                                        {{-- Tombol View dan Buy sesuai SRS --}}
                                        <div class="mt-auto">
                                            <div class="d-grid gap-1">
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewProduk({{ $produk->id }})">
                                                    View
                                                </button>
                                                <button class="btn btn-sm btn-success" onclick="showBuyForm({{ $produk->id }}, '{{ $produk->nama }}')">
                                                    Buy
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-center">
                            {{ $produks->appends(request()->query())->links() }}
                        </div>
                    </div>
                @else
                    {{-- Tampilan jika tidak ada produk --}}
                    <div class="text-center py-5">
                        <i class="bi bi-box" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3">
                            @if(request()->has('kategori_id'))
                                Tidak Ada Produk dalam Kategori Ini
                            @else
                                Pilih Kategori
                            @endif
                        </h4>
                        <p class="text-muted">
                            @if(request()->has('kategori_id'))
                                Produk dalam kategori ini sedang kosong atau belum tersedia.
                            @else
                                Pilih kategori di atas untuk melihat produk yang tersedia.
                            @endif
                        </p>
                        @if(request()->has('kategori_id'))
                            <a href="{{ route('home') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Lihat Semua Produk
                            </a>
                        @endif
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
                            <span class="badge bg-primary rounded-pill float-end">All</span>
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

            {{-- Section Feedback untuk Customer --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-chat-heart"></i> Feedback
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Berikan feedback pengalaman berbelanja Anda.</p>
                    <form action="{{ route('customer.feedback') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <textarea name="message" 
                                      class="form-control @error('message') is-invalid @enderror" 
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
                <button type="button" class="btn btn-success" id="buyFromModal">
                    <i class="bi bi-cart-plus"></i> Masukkan ke Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal untuk Buy Form --}}
<div class="modal fade" id="buyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Masukkan ke Keranjang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="buyForm" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="produk_nama" class="form-label">Produk</label>
                        <input type="text" id="produk_nama" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" 
                               id="jumlah" 
                               name="jumlah" 
                               class="form-control" 
                               value="1" 
                               min="1" 
                               max="10" 
                               required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="submitBuyForm()">
                    <i class="bi bi-cart-plus"></i> Masukkan ke Keranjang
                </button>
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
                <button type="button" class="btn btn-primary" id="lihatProdukKategori">
                    Lihat Produk dalam Kategori
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Variable untuk menyimpan data produk saat ini
let currentProdukId = null;

// Fungsi untuk view detail produk via AJAX
function viewProduk(id) {
    currentProdukId = id;
    
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
                        <h4 class="text-success mb-3">${data.harga_formatted}</h4>
                        <p><strong>Kategori:</strong> ${data.kategori}</p>
                        <p><strong>Toko:</strong> ${data.toko}</p>
                        <div class="alert alert-info">
                            <h6>Deskripsi:</h6>
                            <p class="mb-0">${data.deskripsi}</p>
                        </div>
                    </div>
                </div>
            `;
            
            // Update buy button in modal
            document.getElementById('buyFromModal').onclick = function() {
                showBuyForm(id, data.nama);
            };
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

// Fungsi untuk menampilkan form buy
function showBuyForm(produkId, produkNama) {
    // Set form action
    document.getElementById('buyForm').action = `/keranjang/tambah/${produkId}`;
    
    // Set produk nama
    document.getElementById('produk_nama').value = produkNama;
    
    // Reset jumlah
    document.getElementById('jumlah').value = 1;
    
    // Hide produk detail modal if open
    const produkModal = bootstrap.Modal.getInstance(document.getElementById('produkDetailModal'));
    if (produkModal) {
        produkModal.hide();
    }
    
    // Show buy modal
    const buyModal = new bootstrap.Modal(document.getElementById('buyModal'));
    buyModal.show();
}

// Fungsi untuk submit form buy
function submitBuyForm() {
    document.getElementById('buyForm').submit();
}

// Fungsi untuk view detail kategori
function viewKategori(id) {
    // Find kategori data dari server-side data
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
                    <p class="mb-0"><strong>Harga Mulai:</strong> Rp {{ number_format($kategori->harga, 0, ',', '.') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-muted">
                        <i class="bi bi-box"></i>
                        Tersedia {{ $kategori->produks->count() }} produk dalam kategori ini
                    </p>
                </div>
            `;
            
            // Update button to show products in category
            document.getElementById('lihatProdukKategori').onclick = function() {
                window.location.href = `{{ route('home') }}?kategori_id={{ $kategori->id }}`;
            };
            
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