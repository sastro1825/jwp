@extends('layouts.app')

{{-- Section konten utama --}}
@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            {{-- Judul halaman --}}
            <h1 class="mb-4">Keranjang Belanja</h1>
            <p class="text-muted">
                Kelola produk dalam keranjang Anda sebelum checkout
                @if(auth()->user()->role === 'pemilik_toko')
                    {{-- Badge untuk pemilik toko --}}
                    <span class="badge bg-success ms-2">Pemilik Toko</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Pesan sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Pesan error --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Navigasi breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('customer.area') }}">Customer Area</a>
            </li>
            <li class="breadcrumb-item active">Keranjang Belanja</li>
        </ol>
    </nav>

    @if($keranjangItems->count() > 0)
        {{-- Tabel daftar item keranjang --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-cart3"></i> Item dalam Keranjang ({{ $keranjangItems->count() }} produk)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-center">Subtotal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($keranjangItems as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            {{-- Logika penampilan gambar berdasarkan tipe item --}}
                                            @if($item->item_type === 'toko_kategori')
                                                @php
                                                    // Mengambil nama kategori dari nama item
                                                    $namaKategori = explode(' (Toko:', $item->nama_item)[0];
                                                    $tokoKategori = \App\Models\TokoKategori::where('nama', $namaKategori)->first();
                                                @endphp
                                                @if($tokoKategori && $tokoKategori->gambar)
                                                    {{-- Gambar kategori toko --}}
                                                    <img src="{{ asset('storage/' . $tokoKategori->gambar) }}" 
                                                         alt="{{ $item->nama_item }}" 
                                                         class="rounded"
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                    {{-- Placeholder jika gambar toko tidak ada --}}
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="bi bi-shop text-success"></i>
                                                    </div>
                                                @endif
                                            @elseif($item->kategori && $item->kategori->gambar)
                                                {{-- Gambar kategori admin --}}
                                                <img src="{{ asset('storage/' . $item->kategori->gambar) }}" 
                                                     alt="{{ $item->nama_item }}" 
                                                     class="rounded"
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                {{-- Placeholder jika gambar tidak ada --}}
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="bi bi-heart-pulse text-primary"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $item->nama_item }}</h6>
                                            {{-- Waktu penambahan item dengan timezone Jakarta --}}
                                            <small class="text-muted">Ditambahkan: {{ $item->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{-- Penampilan kategori berdasarkan tipe item --}}
                                    @if($item->item_type === 'toko_kategori')
                                        <span class="badge bg-success">Toko Mitra</span>
                                        <br><small class="text-muted">{{ explode(' (Toko:', $item->nama_item)[0] }}</small>
                                    @elseif($item->kategori)
                                        <span class="badge bg-info">{{ $item->kategori->getCategoryTypeLabel() }}</span>
                                        <br><small class="text-muted">{{ $item->kategori->nama }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <strong>Rp {{ number_format((float)$item->harga_item, 0, ',', '.') }}</strong>
                                </td>
                                <td class="text-center">
                                    {{-- Form untuk memperbarui jumlah item --}}
                                    <form action="{{ 
                                        auth()->user()->role === 'pemilik_toko' 
                                            ? route('pemilik-toko.keranjang.update', $item->id) 
                                            : route('customer.keranjang.update', $item->id) 
                                    }}" method="POST" class="d-inline update-form">
                                        @csrf
                                        @method('PATCH')
                                        <div class="input-group" style="width: 120px;">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="decreaseQuantity({{ $item->id }})">-</button>
                                            <input type="number" 
                                                   name="jumlah" 
                                                   id="jumlah_{{ $item->id }}"
                                                   value="{{ $item->jumlah }}" 
                                                   min="1" 
                                                   max="100"
                                                   class="form-control form-control-sm text-center"
                                                   onchange="this.form.submit()">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="increaseQuantity({{ $item->id }})">+</button>
                                        </div>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <strong class="text-success">
                                        Rp {{ number_format((float)$item->harga_item * (int)$item->jumlah, 0, ',', '.') }}
                                    </strong>
                                </td>
                                <td class="text-center">
                                    {{-- Form untuk menghapus item --}}
                                    <form action="{{ 
                                        auth()->user()->role === 'pemilik_toko' 
                                            ? route('pemilik-toko.keranjang.hapus', $item->id) 
                                            : route('customer.keranjang.hapus', $item->id) 
                                    }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Yakin hapus {{ $item->nama_item }} dari keranjang?')"
                                                title="Hapus dari keranjang">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Ringkasan dan checkout --}}
        <div class="row mt-4">
            <div class="col-md-8">
                {{-- Informasi pengiriman --}}
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-truck"></i> Informasi Pengiriman</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Pengiriman Gratis!</strong> Untuk semua wilayah Indonesia.
                        </div>
                        <p class="text-muted mb-0">
                            Estimasi pengiriman: 2-5 hari kerja<br>
                            Alamat akan dikonfirmasi saat checkout
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                {{-- Ringkasan pesanan dan tombol checkout --}}
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-calculator"></i> Ringkasan Pesanan</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal ({{ $keranjangItems->sum('jumlah') }} item):</span>
                            <span>Rp {{ number_format((float)$totalHarga, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Ongkos Kirim:</span>
                            <span class="text-success">Gratis</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-primary">Rp {{ number_format((float)$totalHarga, 0, ',', '.') }}</strong>
                        </div>
                        
                        {{-- Tombol untuk membuka modal checkout --}}
                        <div class="d-grid">
                            <button type="button" 
                                    class="btn btn-primary btn-lg" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#checkoutModal">
                                <i class="bi bi-credit-card"></i> Checkout Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informasi debug jika mode debug aktif --}}
        @if(config('app.debug'))
            <div class="alert alert-info mt-3">
                <strong>Debug COD Info:</strong><br>
                User City: {{ auth()->user()->city ?? 'Tidak ada' }}<br>
                Admin City: {{ \App\Models\User::where('role', 'admin')->first()->city ?? 'Tidak ada' }}<br>
                Can Use COD: {{ ($canUseCOD ?? false) ? 'Ya' : 'Tidak' }}<br>
                Toko Items: {{ $keranjangItems->where('item_type', 'toko_kategori')->count() }}
            </div>
        @endif
    @else
        {{-- Tampilan jika keranjang kosong --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-cart-x" style="font-size: 5rem; color: #ccc;"></i>
                <h4 class="mt-3">Keranjang Belanja Kosong</h4>
                <p class="text-muted">Belum ada produk dalam keranjang Anda. Ayo mulai berbelanja!</p>
                <a href="{{ route('customer.area') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Mulai Berbelanja
                </a>
            </div>
        </div>
    @endif

    {{-- Tombol navigasi tambahan --}}
    <div class="mt-4">
        <a href="{{ route('customer.area') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Lanjut Belanja
        </a>
        
        @if(auth()->user()->role === 'pemilik_toko')
            <a href="{{ route('pemilik-toko.order.history') }}" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-clock-history"></i> Riwayat Pesanan
            </a>
        @else
            <a href="{{ route('customer.order.history') }}" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-clock-history"></i> Riwayat Pesanan
            </a>
        @endif
    </div>
</div>

{{-- Modal untuk proses checkout --}}
@if($keranjangItems->count() > 0)
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ 
                auth()->user()->role === 'pemilik_toko' 
                    ? route('pemilik-toko.checkout') 
                    : route('customer.checkout') 
            }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-credit-card"></i> Checkout Pesanan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            {{-- Form informasi pengiriman --}}
                            <h6>Informasi Pengiriman</h6>
                            <div class="mb-3">
                                <label for="alamat_pengiriman" class="form-label">Alamat Pengiriman <span class="text-danger">*</span></label>
                                <textarea id="alamat_pengiriman" 
                                          name="alamat_pengiriman" 
                                          class="form-control" 
                                          rows="4" 
                                          required
                                          placeholder="Masukkan alamat lengkap untuk pengiriman...">{{ auth()->user()->getFullAddress() }}</textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                <select id="metode_pembayaran" name="metode_pembayaran" class="form-select" required>
                                    <option value="">Pilih Metode Pembayaran</option>
                                    <option value="prepaid">Prepaid (Bayar Dimuka)</option>
                                    @if($canUseCOD ?? false)
                                        <option value="postpaid">COD (Cash On Delivery)</option>
                                    @endif
                                </select>
                                
                                @if(!($canUseCOD ?? false))
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> 
                                        COD tidak tersedia karena ada toko yang berbeda kota dengan Anda
                                    </small>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                                <textarea id="catatan" 
                                          name="catatan" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Catatan khusus untuk pesanan..."></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            {{-- Ringkasan pesanan di modal --}}
                            <h6>Ringkasan Pesanan</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    @foreach($keranjangItems as $item)
                                        <div class="d-flex justify-content-between mb-2">
                                            <div>
                                                <strong>{{ $item->nama_item }}</strong><br>
                                                <small class="text-muted">{{ $item->jumlah }}x @ Rp {{ number_format((float)$item->harga_item, 0, ',', '.') }}</small>
                                            </div>
                                            <div class="text-end">
                                                <strong>Rp {{ number_format((float)$item->harga_item * (int)$item->jumlah, 0, ',', '.') }}</strong>
                                            </div>
                                        </div>
                                        @if(!$loop->last)<hr class="my-2">@endif
                                    @endforeach
                                    
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span>Ongkos Kirim:</span>
                                        <span class="text-success">Gratis</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total Pembayaran:</strong>
                                        <strong class="text-primary">Rp {{ number_format((float)$totalHarga, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Perhatian:</strong> Pastikan data pengiriman sudah benar sebelum melanjutkan checkout.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Konfirmasi Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

{{-- Script JavaScript untuk interaksi --}}
@push('scripts')
<script>
// Fungsi untuk menambah jumlah item
function increaseQuantity(itemId) {
    const input = document.getElementById('jumlah_' + itemId);
    const currentValue = parseInt(input.value);
    if (currentValue < 100) {
        input.value = currentValue + 1;
        input.form.submit();
    }
}

// Fungsi untuk mengurangi jumlah item
function decreaseQuantity(itemId) {
    const input = document.getElementById('jumlah_' + itemId);
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
        input.form.submit();
    }
}

// Inisialisasi saat dokumen dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Menangani submit otomatis pada form update jumlah
    const updateForms = document.querySelectorAll('.update-form');
    updateForms.forEach(form => {
        const submitBtn = form.querySelector('input[name="jumlah"]');
        submitBtn.addEventListener('change', function() {
            // Menambahkan efek loading
            this.style.opacity = '0.5';
            this.disabled = true;
            form.submit();
        });
    });

    // Menyembunyikan alert secara otomatis setelah 5 detik
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });

    // Validasi form checkout
    const checkoutForm = document.querySelector('#checkoutModal form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const alamat = document.getElementById('alamat_pengiriman').value.trim();
            const metode = document.getElementById('metode_pembayaran').value;
            
            // Validasi alamat dan metode pembayaran
            if (!alamat || !metode) {
                e.preventDefault();
                alert('Mohon lengkapi alamat pengiriman dan metode pembayaran.');
                return false;
            }

            // Validasi COD jika tidak tersedia
            @if(!($canUseCOD ?? false))
                if (metode === 'postpaid') {
                    e.preventDefault();
                    alert('COD tidak tersedia karena Anda tidak se-kota dengan admin/toko. Silakan pilih prepaid.');
                    return false;
                }
            @endif
            
            // Efek loading pada tombol submit
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
        });
        
        // Validasi real-time untuk metode pembayaran
        const metodeSelect = document.getElementById('metode_pembayaran');
        if (metodeSelect) {
            metodeSelect.addEventListener('change', function() {
                @if(!($canUseCOD ?? false))
                    if (this.value === 'postpaid') {
                        this.value = '';
                        alert('COD tidak tersedia karena Anda tidak se-kota dengan admin/toko.');
                    }
                @endif
            });
        }
    }
});
</script>
@endpush