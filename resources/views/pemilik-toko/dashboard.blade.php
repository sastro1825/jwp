@extends('layouts.app')

@section('content')
{{-- Dashboard Pemilik Toko --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Dashboard Pemilik Toko</h1>
            <p class="text-muted">Selamat datang, {{ auth()->user()->name }}! Kelola toko online Anda di OSS.</p>
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

    {{-- Info Toko --}}
    @php
        $toko = auth()->user()->toko;
    @endphp

    @if($toko)
        <div class="alert alert-success">
            <h5><i class="bi bi-shop"></i> Toko Aktif: {{ $toko->nama }}</h5>
            <p class="mb-0">Status: <span class="badge bg-success">{{ ucfirst($toko->status) }}</span> | Kategori: {{ ucwords(str_replace('-', ' ', $toko->kategori_usaha)) }}</p>
        </div>
    @else
        <div class="alert alert-info">
            <h5><i class="bi bi-info-circle"></i> Info Toko</h5>
            <p class="mb-0">Data toko belum tersinkronisasi. Silakan hubungi admin jika ada masalah.</p>
        </div>
    @endif

    {{-- Menu Pemilik Toko - Hanya Kategori dan Pengiriman --}}
    <div class="row">
        <div class="col-12">
            <h3 class="mb-3">Menu Kelola Toko</h3>
        </div>
        
        {{-- Menu Card Kelola Kategori --}}
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
        
        {{-- Menu Card Kelola Pengiriman --}}
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

    {{-- Statistik Toko --}}
    <div class="row mt-4">
        <div class="col-12">
            <h3 class="mb-3">Statistik Toko</h3>
        </div>
        
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

    {{-- Link kembali ke customer area dengan route yang benar --}}
    <div class="mt-4">
        <a href="{{ route('customer.area') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Kembali ke Area Customer
        </a>
        <a href="{{ route('pemilik-toko.keranjang') }}" class="btn btn-outline-success ms-2">
            <i class="bi bi-cart3"></i> Keranjang Belanja
            @php
                $jumlahKeranjang = \App\Models\Keranjang::where('user_id', auth()->id())->sum('jumlah');
            @endphp
            @if($jumlahKeranjang > 0)
                <span class="badge bg-danger">{{ $jumlahKeranjang }}</span>
            @endif
        </a>
        <a href="{{ route('pemilik-toko.order.history') }}" class="btn btn-outline-info ms-2">
            <i class="bi bi-clock-history"></i> Riwayat Pesanan
        </a>
    </div>
</div>

{{-- Modal Profile --}}
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
                    <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="rounded-circle" width="80" height="80">
                    <h5 class="mt-2">{{ auth()->user()->name }}</h5>
                    <span class="badge bg-success">Pemilik Toko</span>
                </div>
                
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
@endsection

@push('scripts')
<script>
// Auto refresh statistik setiap 30 detik
setInterval(function() {
    // Refresh statistik keranjang
    if (document.querySelector('.badge.bg-danger')) {
        fetch('{{ route("pemilik-toko.keranjang.data") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.totalItems > 0) {
                    const badges = document.querySelectorAll('.badge.bg-danger');
                    badges.forEach(badge => {
                        badge.textContent = data.totalItems;
                    });
                }
            })
            .catch(error => console.log('Failed to refresh cart data'));
    }
}, 30000);

// Auto hide alerts
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