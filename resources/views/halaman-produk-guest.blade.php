@extends('layouts.app')

@section('content')
{{-- Halaman Produk untuk Visitor/Guest sesuai SRS --}}
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Title untuk guest --}}
            <h1 class="mb-4">Product Page - Tukupedia</h1>
            <p class="text-muted">Selamat datang di Tukupedia! Silakan <a href="{{ route('register') }}" class="text-primary">daftar</a> atau <a href="{{ route('login') }}" class="text-primary">login</a> untuk mulai berbelanja.</p>
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
                                {{-- Gambar Kategori tanpa lazy loading --}}
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
                                    
                                    {{-- Badge jenis kategori --}}
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-info">{{ $kategori->getCategoryTypeLabel() }}</span>
                                    </div>
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    {{-- Nama Kategori --}}
                                    <h6 class="card-title text-center">{{ $kategori->nama }}</h6>
                                    
                                    {{-- Harga Patokan - DIPERBAIKI --}}
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
            
            {{-- Kategori dari Toko-toko untuk Guest - PERBAIKAN LANGSUNG MUNCUL --}}
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

        {{-- Sidebar Kategori (Kolom Kanan) - DIPERBAIKI --}}
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

@push('scripts')
<script>
// Fungsi untuk view detail kategori admin
function viewKategori(id) {
    @foreach($kategoris as $kategori)
        if (id === {{ $kategori->id }}) {
            const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
            document.getElementById('kategoriDetailContent').innerHTML = `
                <div class="text-center mb-3">
                    @if($kategori->gambar && file_exists(storage_path('app/public/' . $kategori->gambar)))
                        <img src="{{ asset('storage/' . $kategori->gambar) }}"
                             class="img-fluid mb-3"
                             style="max-height: 200px;"
                             alt="{{ $kategori->nama }}">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center mb-3" 
                             style="height: 200px;">
                            <div class="text-center">
                                <i class="bi bi-heart-pulse text-primary" style="font-size: 4rem;"></i>
                                <br><small class="text-muted">Belum Ada Gambar</small>
                            </div>
                        </div>
                    @endif
                    <h5>{{ $kategori->nama }}</h5>
                    <span class="badge bg-info mb-2">{{ $kategori->getCategoryTypeLabel() }}</span>
                </div>
                <div class="alert alert-info">
                    <h6>Deskripsi:</h6>
                    <p class="mb-2">{{ $kategori->deskripsi }}</p>
                    <p class="mb-0"><strong>Harga:</strong> Rp {{ number_format($kategori->harga, 0, ',', '.') }}</p>
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
                        @if($kategori->gambar && file_exists(storage_path('app/public/' . $kategori->gambar)))
                            <img src="{{ asset('storage/' . $kategori->gambar) }}"
                                 class="img-fluid mb-3"
                                 style="max-height: 200px;"
                                 alt="{{ $kategori->nama }}">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center mb-3" 
                                 style="height: 200px;">
                                <div class="text-center">
                                    <i class="bi bi-shop text-success" style="font-size: 4rem;"></i>
                                    <br><small class="text-muted">Belum Ada Gambar</small>
                                </div>
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
</script>
@endpush