@extends('layouts.app')

@section('content')
{{-- Halaman Produk untuk Customer yang sudah login sesuai SRS --}}
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Product Page - Toko Alat Kesehatan</h1>
            <p class="text-muted">Selamat datang, {{ auth()->user()->name }}! Pilih kategori produk kesehatan yang Anda butuhkan.</p>
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

    {{-- Quick Menu untuk Customer - Hapus My Account, tambah Riwayat Pesanan --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Menu Customer</h5>
                </div>
                <div>
                    {{-- Link ke keranjang belanja dengan badge jumlah item --}}
                    <a href="{{ route('customer.keranjang') }}" class="btn btn-primary me-2">
                        <i class="bi bi-cart3"></i> Keranjang Belanja
                        @php
                            $jumlahKeranjang = \App\Models\Keranjang::where('user_id', auth()->id())->sum('jumlah');
                        @endphp
                        @if($jumlahKeranjang > 0)
                            <span class="badge bg-danger">{{ $jumlahKeranjang }}</span>
                        @endif
                    </a>
                    {{-- Link ke riwayat pesanan (ganti My Account) --}}
                    <a href="{{ route('customer.order.history') }}" class="btn btn-secondary">
                        <i class="bi bi-clock-history"></i> Riwayat Pesanan
                    </a>
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
                                    
                                    {{-- Tombol View dan Buy sesuai SRS --}}
                                    <div class="mt-auto">
                                        <div class="d-grid gap-2">
                                            {{-- Tombol View untuk melihat detail kategori --}}
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    onclick="viewKategori({{ $kategori->id }})">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            
                                            {{-- Tombol Buy untuk buy langsung dari kategori --}}
                                            <form action="{{ route('customer.buy.from.kategori', $kategori->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                                    <i class="bi bi-bag-plus"></i> Buy
                                                </button>
                                            </form>
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
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-chevron-right me-2"></i>
                                        {{ $kategori->nama }}
                                    </div>
                                    {{-- Tombol Buy Mini di Sidebar --}}
                                    <form action="{{ route('customer.buy.from.kategori', $kategori->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Buy {{ $kategori->nama }}">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                    </form>
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
                                <a href="{{ route('customer.keranjang') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-cart-check"></i> Lihat Keranjang
                                </a>
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

@push('scripts')
<script>
// Variable untuk menyimpan kategori yang sedang dilihat
let currentKategoriId = null;

// Fungsi untuk view detail kategori
function viewKategori(id) {
    currentKategoriId = id;
    
    // Find kategori data dari server-side data
    @foreach($kategoris as $kategori)
        if (id === {{ $kategori->id }}) {
            const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
            document.getElementById('kategoriDetailContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center mb-3">
                            @if($kategori->gambar)
                                <img src="{{ asset('storage/' . $kategori->gambar) }}" class="img-fluid rounded" style="max-height: 250px;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 250px;">
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
            
            // Update button untuk buy dari modal dengan parameter yang benar
            document.getElementById('buyFromModal').onclick = function() {
                // Submit form buy dari kategori dengan parameter yang benar
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url("buy-from-kategori") }}/' + {{ $kategori->id }};
                
                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = '{{ csrf_token() }}';
                form.appendChild(token);
                
                document.body.appendChild(form);
                form.submit();
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
    
    // Add loading state untuk tombol buy
    const buyForms = document.querySelectorAll('form[action*="buy-from-kategori"]');
    buyForms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
        });
    });
});

// Function untuk refresh keranjang stats
function refreshKeranjangStats() {
    // Reload bagian status keranjang jika diperlukan
    fetch('{{ route("customer.keranjang") }}')
        .then(response => response.text())
        .then(data => {
            // Update counter di navbar jika ada
            const cartBadge = document.querySelector('.badge.bg-danger');
            if (cartBadge) {
                // Extract jumlah dari response atau reload page
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            console.log('Failed to refresh cart stats:', error);
        });
}
</script>
@endpush