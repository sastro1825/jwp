@extends('layouts.app')

@section('content')
    {{-- Kontainer utama untuk halaman produk --}}
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                {{-- Judul halaman untuk customer --}}
                <h1 class="mb-4">Product Page - Tukupedia</h1>
                <p class="text-muted">
                    {{-- Menampilkan nama pengguna yang sedang login --}}
                    Selamat datang, {{ auth()->user()->name }}! 
                    @if(auth()->user()->role === 'pemilik_toko')
                        {{-- Pesan khusus untuk pemilik toko --}}
                        Anda login sebagai <span class="badge bg-success">Pemilik Toko</span>. Anda dapat berbelanja dan mengelola toko.
                    @else
                        {{-- Pesan untuk customer biasa --}}
                        Pilih kategori produk kesehatan yang Anda butuhkan.
                    @endif
                </p>
            </div>
        </div>

        {{-- Menampilkan pesan sukses jika ada --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Menampilkan pesan error jika ada --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Menu cepat untuk customer --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="mb-0">Menu Customer</h5>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        {{-- Tombol menuju keranjang belanja --}}
                        <a href="{{ 
                            auth()->user()->role === 'pemilik_toko' 
                                ? route('pemilik-toko.keranjang') 
                                : route('customer.keranjang') 
                        }}" class="btn btn-primary">
                            <i class="bi bi-cart3"></i> Keranjang Belanja
                            @php
                                // Menghitung jumlah item di keranjang
                                $jumlahKeranjang = \App\Models\Keranjang::where('user_id', auth()->id())->sum('jumlah');
                            @endphp
                            @if($jumlahKeranjang > 0)
                                {{-- Menampilkan badge jumlah item jika ada --}}
                                <span class="badge bg-danger">{{ $jumlahKeranjang }}</span>
                            @endif
                        </a>
                        {{-- Tombol menuju riwayat pesanan --}}
                        <a href="{{ 
                            auth()->user()->role === 'pemilik_toko' 
                                ? route('pemilik-toko.order.history') 
                                : route('customer.order.history') 
                        }}" class="btn btn-secondary">
                            <i class="bi bi-clock-history"></i> Riwayat Pesanan
                        </a>
                        @if(auth()->user()->role === 'customer')
                            @if(!auth()->user()->hasPendingTokoRequest() && !auth()->user()->hasApprovedToko())
                                {{-- Tombol untuk mengajukan toko baru --}}
                                <a href="{{ route('customer.toko.request') }}" class="btn btn-warning">
                                    <i class="bi bi-shop"></i> Ajukan Toko
                                </a>
                            @else
                                {{-- Tombol untuk melihat status toko --}}
                                <a href="{{ route('customer.toko.status') }}" class="btn btn-info">
                                    <i class="bi bi-shop"></i> Status Toko
                                </a>
                            @endif
                        @elseif(auth()->user()->role === 'pemilik_toko')
                            {{-- Tombol menuju dashboard pemilik toko --}}
                            <a href="{{ route('pemilik-toko.dashboard') }}" class="btn btn-success">
                                <i class="bi bi-shop"></i> Dashboard Toko
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Konten utama dengan layout sesuai SRS --}}
        <div class="row">
            {{-- Area tampilan produk (kolom kiri) untuk kategori dan toko mitra --}}
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
                                                {{-- Menampilkan gambar kategori jika ada --}}
                                                <img src="{{ asset('storage/' . $kategori->gambar) }}"
                                                     alt="{{ $kategori->nama }}"
                                                     class="card-img-top"
                                                     style="width: 100%; height: 200px; object-fit: cover;">
                                            @else
                                                {{-- Placeholder jika gambar tidak tersedia --}}
                                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                                     style="height: 200px;">
                                                    <div class="text-center">
                                                        <i class="bi bi-heart-pulse text-primary" style="font-size: 3rem;"></i>
                                                        <br><small class="text-muted">Belum Ada Gambar</small>
                                                    </div>
                                                </div>
                                            @endif
                                            {{-- Label tipe kategori --}}
                                            <div class="position-absolute top-0 end-0 m-2">
                                                <span class="badge bg-info">{{ $kategori->getCategoryTypeLabel() }}</span>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title text-center">{{ $kategori->nama }}</h6>
                                            <div class="text-center mb-3">
                                                {{-- Harga kategori dalam format Rupiah --}}
                                                <span class="badge bg-success fs-6">
                                                    Rp {{ number_format($kategori->harga, 0, ',', '.') }}
                                                </span>
                                            </div>
                                            @if($kategori->deskripsi)
                                                {{-- Deskripsi singkat kategori --}}
                                                <p class="card-text text-muted small text-center">
                                                    {{ Str::limit($kategori->deskripsi, 50) }}
                                                </p>
                                            @endif
                                            <div class="mt-auto">
                                                <div class="d-grid gap-2">
                                                    {{-- Tombol untuk melihat detail kategori --}}
                                                    <button class="btn btn-outline-primary btn-sm"
                                                            onclick="viewKategori({{ $kategori->id }})">
                                                        <i class="bi bi-eye"></i> View
                                                    </button>
                                                    @if(auth()->user()->role === 'pemilik_toko')
                                                        {{-- Form beli untuk pemilik toko --}}
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
                                                        {{-- Form beli untuk customer --}}
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

                    {{-- Menampilkan kategori dari toko mitra --}}
                    @if(isset($kategoriToko) && $kategoriToko->count() > 0)
                        <div class="mb-4 mt-5">
                            <h4>Kategori dari Toko Mitra</h4>
                            <div class="row">
                                @foreach($kategoriToko as $kategori)
                                    <div class="col-md-4 col-lg-3 mb-3">
                                        <div class="card h-100 kategori-card shadow-sm border-success">
                                            <div class="position-relative">
                                                @if($kategori->gambar && file_exists(storage_path('app/public/' . $kategori->gambar)))
                                                    {{-- Menampilkan gambar kategori toko jika ada --}}
                                                    <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                                                         alt="{{ $kategori->nama }}" 
                                                         class="card-img-top"
                                                         style="height: 200px; object-fit: cover; width: 100%;">
                                                @else
                                                    {{-- Placeholder untuk gambar kategori toko --}}
                                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                                         style="height: 200px;">
                                                        <div class="text-center">
                                                            <i class="bi bi-shop text-success" style="font-size: 3rem;"></i>
                                                            <br><small class="text-muted">Belum Ada Gambar</small>
                                                        </div>
                                                    </div>
                                                @endif
                                                {{-- Label nama toko --}}
                                                <div class="position-absolute top-0 start-0 m-2">
                                                    <span class="badge bg-success">{{ $kategori->toko->nama ?? 'Toko' }}</span>
                                                </div>
                                                {{-- Label tipe kategori toko --}}
                                                <div class="position-absolute top-0 end-0 m-2">
                                                    <span class="badge bg-warning">{{ $kategori->getCategoryTypeLabel() }}</span>
                                                </div>
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <h6 class="card-title text-center">{{ $kategori->nama }}</h6>
                                                <div class="text-center mb-3">
                                                    {{-- Harga kategori toko dalam format Rupiah --}}
                                                    <span class="badge bg-success fs-6">
                                                        Rp {{ number_format($kategori->harga, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                                @if($kategori->deskripsi)
                                                    {{-- Deskripsi singkat kategori toko --}}
                                                    <p class="card-text text-muted small text-center">
                                                        {{ Str::limit($kategori->deskripsi, 50) }}
                                                    </p>
                                                @endif
                                                {{-- Nama toko penyedia kategori --}}
                                                <small class="text-center text-muted mb-2">
                                                    Oleh: {{ $kategori->toko->nama ?? 'Toko Tidak Diketahui' }}
                                                </small>
                                                <div class="mt-auto">
                                                    <div class="d-grid gap-2">
                                                        {{-- Tombol untuk melihat detail kategori toko --}}
                                                        <button class="btn btn-outline-success btn-sm"
                                                                onclick="viewKategoriToko({{ $kategori->id }})">
                                                            <i class="bi bi-eye"></i> View Toko
                                                        </button>
                                                        @if(auth()->user()->role === 'pemilik_toko')
                                                            {{-- Form beli dari toko untuk pemilik toko --}}
                                                            <form action="{{ route('pemilik-toko.buy.from.toko.kategori', $kategori->id) }}" method="POST" class="buy-form">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success btn-sm w-100">
                                                                    <i class="bi bi-cart-plus"></i> Beli dari Toko
                                                                </button>
                                                            </form>
                                                        @else
                                                            {{-- Form beli dari toko untuk customer --}}
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
                        {{-- Pesan jika tidak ada kategori toko mitra --}}
                        <div class="alert alert-info">
                            <strong>Info:</strong> Tidak ada kategori toko mitra yang tersedia atau belum ada toko yang approved.
                        </div>
                    @endif
                @else
                    {{-- Pesan jika tidak ada kategori produk --}}
                    <div class="text-center py-5">
                        <i class="bi bi-heart-pulse" style="font-size: 5rem; color: #ccc;"></i>
                        <h4 class="mt-3">Kategori Belum Tersedia</h4>
                        <p class="text-muted">Silakan hubungi admin untuk menambahkan kategori produk.</p>
                    </div>
                @endif
            </div>

            {{-- Sidebar kategori di kolom kanan --}}
            <div class="col-md-3">
                <div class="card product-category-card">
                    <div class="card-header">
                        <h5 class="mb-0">Product Category</h5>
                    </div>
                    <div class="card-body category-list-container">
                        <div class="list-group list-group-flush category-list">
                            {{-- Menampilkan semua kategori dengan jumlah total --}}
                            <div class="list-group-item list-group-item-action active">
                                <i class="bi bi-grid-3x3-gap me-2"></i>
                                Semua Kategori
                                <span class="badge bg-primary rounded-pill float-end">{{ $kategoris->count() }}</span>
                            </div>
                            @php
                                // Mengelompokkan kategori berdasarkan tipe
                                $categoryTypes = $kategoris->groupBy('category_type');
                            @endphp
                            @foreach($categoryTypes as $type => $kategorisByType)
                                {{-- Label untuk tipe kategori --}}
                                <div class="list-group-item bg-light">
                                    <small class="text-muted fw-bold">{{ ucwords(str_replace('-', ' ', $type)) }}</small>
                                </div>
                                @foreach($kategorisByType as $kategori)
                                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-chevron-right me-2"></i>
                                            {{ $kategori->nama }}
                                        </div>
                                        @if(auth()->user()->role === 'pemilik_toko')
                                            {{-- Form beli di sidebar untuk pemilik toko --}}
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
                                            {{-- Form beli di sidebar untuk customer --}}
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

                {{-- Status keranjang belanja --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-cart3"></i> Status Keranjang
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            // Mengambil item keranjang pengguna
                            $keranjangItems = \App\Models\Keranjang::where('user_id', auth()->id())->get();
                            // Menghitung total item di keranjang
                            $totalItems = $keranjangItems->sum('jumlah');
                            // Menghitung total harga di keranjang
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
                                        {{-- Tombol lihat keranjang untuk pemilik toko --}}
                                        <a href="{{ route('pemilik-toko.keranjang') }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-cart-check"></i> Lihat Keranjang
                                        </a>
                                    @else
                                        {{-- Tombol lihat keranjang untuk customer --}}
                                        <a href="{{ route('customer.keranjang') }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-cart-check"></i> Lihat Keranjang
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- Pesan jika keranjang kosong --}}
                            <div class="text-center text-muted">
                                <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                                <p class="mb-0 mt-2">Keranjang kosong</p>
                                <small>Pilih kategori untuk berbelanja</small>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Form untuk memberikan feedback --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-chat-heart"></i> Feedback
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">Berikan feedback pengalaman berbelanja Anda.</p>
                        @if(auth()->user()->role === 'pemilik_toko')
                            {{-- Form feedback untuk pemilik toko --}}
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
                            {{-- Form feedback untuk customer --}}
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
                    {{-- Status toko untuk customer --}}
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
                    {{-- Informasi toko untuk pemilik toko --}}
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
                                    {{-- Tombol dashboard pemilik toko --}}
                                    <a href="{{ route('pemilik-toko.dashboard') }}" class="btn btn-success btn-sm">
                                        <i class="bi bi-speedometer2"></i> Dashboard Toko
                                    </a>
                                    {{-- Tombol kelola kategori toko --}}
                                    <a href="{{ route('pemilik-toko.kategori') }}" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-tags"></i> Kelola Kategori
                                    </a>
                                    {{-- Tombol kelola pengiriman --}}
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

    {{-- Modal untuk menampilkan detail kategori --}}
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
        /* Styling untuk kartu kategori produk */
        .product-category-card {
            max-height: 450px;
            position: relative;
        }

        /* Mengatur tinggi maksimum dan scroll untuk daftar kategori */
        .category-list-container {
            max-height: 380px;
            overflow-y: auto;
            padding: 0;
        }

        /* Mengatur tampilan daftar kategori */
        .category-list {
            max-height: none;
            overflow: visible;
        }

        /* Styling scrollbar untuk browser berbasis Webkit */
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

        /* Styling scrollbar untuk Firefox */
        .category-list-container {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        /* Styling shadow untuk kartu */
        .card {
            margin-bottom: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        /* Efek hover pada kartu kategori */
        .kategori-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        /* Mengatur tinggi minimum kartu kategori */
        .kategori-card {
            min-height: 400px;
        }

        /* Styling tombol kecil */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Penyesuaian responsif untuk layar kecil */
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

        /* Efek hover pada item daftar */
        .list-group-item-action:hover {
            background-color: #f8f9fa;
        }

        /* Styling item daftar aktif */
        .list-group-item.active {
            background-color: #007bff;
            border-color: #007bff;
        }

        /* Posisi badge */
        .float-end {
            float: right !important;
        }

        /* Styling input form di sidebar */
        .form-control-sm {
            font-size: 0.875rem;
        }

        /* Styling alert */
        .alert {
            margin-bottom: 1rem;
        }

        /* Styling header kartu */
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        /* Penyesuaian ikon */
        .bi {
            vertical-align: -0.125em;
        }

        /* Transisi halus untuk elemen */
        .card, .btn, .list-group-item {
            transition: all 0.2s ease-in-out;
        }

        /* Styling badge pada navigasi */
        .badge {
            font-size: 0.75em;
        }

        /* Penyesuaian responsif untuk menu cepat */
        @media (max-width: 576px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }
        }

        /* Styling statistik keranjang */
        .text-center h4.text-primary {
            font-weight: bold;
            font-size: 2rem;
        }

        .text-center h5.text-success {
            font-weight: 600;
        }

        /* Animasi untuk ikon status toko */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .bi-hourglass-split {
            animation: pulse 2s infinite;
        }

        /* Styling badge untuk peran pengguna */
        .badge.bg-success {
            font-size: 0.8em;
            padding: 0.4em 0.6em;
        }

        /* Jarak antar elemen flexbox */
        .gap-2 {
            gap: 0.5rem !important;
        }

        /* Jarak untuk tombol dalam grid */
        .d-grid.gap-2 {
            gap: 0.5rem !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Variabel untuk menyimpan ID kategori yang sedang dilihat
        let currentKategoriId = null;

        // Fungsi untuk menampilkan detail kategori dalam modal
        function viewKategori(id) {
            currentKategoriId = id;
            @foreach($kategoris as $kategori)
                if (id === {{ $kategori->id }}) {
                    // Inisialisasi modal Bootstrap
                    const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
                    // Mengisi konten modal dengan detail kategori
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
                    // Konfigurasi tombol beli di modal
                    const buyBtn = document.getElementById('buyFromModal');
                    buyBtn.style.display = 'block';
                    buyBtn.disabled = false;
                    buyBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Beli Kategori Ini';
                    buyBtn.onclick = function() {
                        // Membuat form untuk submit pembelian
                        const form = document.createElement('form');
                        form.method = 'POST';
                        @if(auth()->user()->role === 'pemilik_toko')
                            // Route untuk pemilik toko
                            form.action = '{{ route("pemilik-toko.buy.from.kategori", ":id") }}'.replace(':id', {{ $kategori->id }});
                        @else
                            // Route untuk customer
                            form.action = '{{ route("customer.buy.from.kategori", ":id") }}'.replace(':id', {{ $kategori->id }});
                        @endif
                        // Menambahkan token CSRF
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

        // Fungsi untuk menampilkan detail kategori toko dalam modal
        function viewKategoriToko(id) {
            @if(isset($kategoriToko))
                @foreach($kategoriToko as $kategori)
                    if (id === {{ $kategori->id }}) {
                        // Inisialisasi modal Bootstrap
                        const modal = new bootstrap.Modal(document.getElementById('kategoriDetailModal'));
                        // Mengisi konten modal dengan detail kategori toko
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
                        // Konfigurasi tombol beli di modal untuk kategori toko
                        const buyBtn = document.getElementById('buyFromModal');
                        buyBtn.style.display = 'block';
                        buyBtn.disabled = false;
                        buyBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Beli dari Toko';
                        buyBtn.onclick = function() {
                            // Membuat form untuk submit pembelian dari toko
                            const form = document.createElement('form');
                            form.method = 'POST';
                            @if(auth()->user()->role === 'pemilik_toko')
                                // Route untuk pemilik toko
                                form.action = '{{ route("pemilik-toko.buy.from.toko.kategori", ":id") }}'.replace(':id', {{ $kategori->id }});
                            @else
                                // Route untuk customer
                                form.action = '{{ route("customer.buy.from.toko.kategori", ":id") }}'.replace(':id', {{ $kategori->id }});
                            @endif
                            // Menambahkan token CSRF
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

        // Event listener saat dokumen selesai dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Menyembunyikan alert secara otomatis setelah 5 detik
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const closeBtn = alert.querySelector('.btn-close');
                    if (closeBtn) closeBtn.click();
                }, 5000);
            });

            // Penanganan form pembelian
            const buyForms = document.querySelectorAll('.buy-form, .buy-form-sidebar');
            buyForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
                        // Mengembalikan tombol ke semula setelah 5 detik jika masih disable
                        setTimeout(() => {
                            if (submitBtn.disabled) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Buy';
                            }
                        }, 5000);
                    }
                });
            });

            // Inisialisasi tooltip
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endpush