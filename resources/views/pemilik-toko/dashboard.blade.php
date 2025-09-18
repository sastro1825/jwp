@extends('layouts.app')

@section('content')
    {{-- Bagian utama dashboard pemilik toko --}}
    <div class="container">
        <div class="row">
            <div class="col-12">
                {{-- Judul dashboard --}}
                <h1 class="mb-4">Dashboard Pemilik Toko</h1>
                {{-- Menampilkan nama pengguna yang login --}}
                <p class="text-muted">Selamat datang, {{ auth()->user()->name }}! Kelola toko online Anda di OSS.</p>
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

        {{-- Tombol kembali ke area customer --}}
        <div class="mt-4">
            <a href="{{ route('customer.area') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Kembali ke Area Customer
            </a>
        </div>

        {{-- Mengambil data toko dari pengguna yang login --}}
        @php
            $toko = auth()->user()->toko;
        @endphp

        {{-- Menampilkan informasi toko jika ada --}}
        @if($toko)
            <div class="alert alert-success mt-4">
                <h5><i class="bi bi-shop"></i> Toko Aktif: {{ $toko->nama }}</h5>
                {{-- Menampilkan status dan kategori toko --}}
                <p class="mb-0">Status: <span class="badge bg-success">{{ ucfirst($toko->status) }}</span> | Kategori: {{ ucwords(str_replace('-', ' ', $toko->kategori_usaha)) }}</p>
            </div>
        @else
            <div class="alert alert-info mt-4">
                <h5><i class="bi bi-info-circle"></i> Info Toko</h5>
                {{-- Pesan jika data toko belum tersedia --}}
                <p class="mb-0">Data toko belum tersinkronisasi. Silakan hubungi admin jika ada masalah.</p>
            </div>
        @endif

        {{-- Menu untuk mengelola toko --}}
        <div class="row">
            <div class="col-12">
                <h3 class="mb-3">Menu Kelola Toko</h3>
            </div>
            
            {{-- Kartu menu untuk kelola kategori --}}
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-tags-fill" style="font-size: 3rem; color: #28a745;"></i>
                        <h5 class="card-title mt-2">Kelola Kategori</h5>
                        <p class="card-text">Lihat dan kelola kategori produk untuk sistem OSS (Read-Only)</p>
                        <a href="{{ route('pemilik-toko.kategori') }}" class="btn btn-success">
                            <i class="bi bi-tags"></i> Kelola Kategori
                        </a>
                    </div>
                </div>
            </div>
            
            {{-- Kartu menu untuk kelola pengiriman --}}
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-truck" style="font-size: 3rem; color: #6c757d;"></i>
                        <h5 class="card-title mt-2">Kelola Pengiriman</h5>
                        <p class="card-text">Monitor pengiriman produk dan update status pengiriman</p>
                        <a href="{{ route('pemilik-toko.shipping') }}" class="btn btn-secondary">
                            <i class="bi bi-truck"></i> Kelola Pengiriman
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistik toko --}}
        <div class="row mt-4">
            <div class="col-12">
                <h3 class="mb-3">Statistik Toko</h3>
            </div>
            
            {{-- Statistik jumlah kategori --}}
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Total Kategori</h5>
                                <h2>{{ \App\Models\Kategori::count() }}</h2>
                                <small>Kategori sistem</small>
                            </div>
                            <div>
                                <i class="bi bi-tags" style="font-size: 3rem; opacity: 0.8;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Statistik pesanan pending --}}
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Pesanan Pending</h5>
                                <h2>{{ \App\Models\ShippingOrder::where('status', 'pending')->count() }}</h2>
                                <small>Menunggu proses</small>
                            </div>
                            <div>
                                <i class="bi bi-hourglass-split" style="font-size: 3rem; opacity: 0.8;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Statistik pesanan dikirim --}}
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Pesanan Dikirim</h5>
                                <h2>{{ \App\Models\ShippingOrder::where('status', 'shipped')->count() }}</h2>
                                <small>Dalam pengiriman</small>
                            </div>
                            <div>
                                <i class="bi bi-truck" style="font-size: 3rem; opacity: 0.8;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Statistik pesanan selesai --}}
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Pesanan Selesai</h5>
                                <h2>{{ \App\Models\ShippingOrder::where('status', 'delivered')->count() }}</h2>
                                <small>Sudah sampai</small>
                            </div>
                            <div>
                                <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.8;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal untuk menampilkan profil pemilik toko --}}
        <div class="modal fade" id="profileModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-person"></i> Profile Pemilik Toko
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            {{-- Menampilkan avatar pengguna --}}
                            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="rounded-circle" width="80" height="80">
                            <h5 class="mt-2">{{ auth()->user()->name }}</h5>
                            <span class="badge bg-success">Pemilik Toko</span>
                        </div>
                        
                        {{-- Tabel informasi pengguna --}}
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ auth()->user()->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Alamat:</strong></td>
                                <td>{{ auth()->user()->getFullAddress() }}</td>
                            </tr>
                            <tr>
                                <td><strong>No. HP:</strong></td>
                                <td>{{ auth()->user()->contact_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Lahir:</strong></td>
                                <td>{{ auth()->user()->formatted_dob ?? '-' }}</td>
                            </tr>
                            @if($toko)
                            <tr>
                                <td><strong>Nama Toko:</strong></td>
                                <td>{{ $toko->nama }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kategori Usaha:</strong></td>
                                <td>{{ ucwords(str_replace('-', ' ', $toko->kategori_usaha)) }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <a href="#" class="btn btn-primary">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Fungsi untuk me-refresh statistik setiap 30 detik
setInterval(function() {
    console.log('Dashboard statistik refreshed');
}, 30000);

// Fungsi untuk menyembunyikan alert secara otomatis setelah 5 detik
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