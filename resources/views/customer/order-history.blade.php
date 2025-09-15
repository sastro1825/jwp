@extends('layouts.app')

@section('content')
{{-- Halaman Riwayat Pesanan Customer --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Riwayat Pesanan Anda</h1>
            <p class="text-muted">Pantau status pesanan dan unduh laporan transaksi</p>
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

    {{-- Info Customer --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-person-circle"></i> Informasi Akun
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nama:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Gender:</strong> {{ $user->gender ? ucfirst($user->gender) : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Kota:</strong> {{ $user->city ?? '-' }}</p>
                    <p><strong>No. HP:</strong> {{ $user->contact_no ?? '-' }}</p>
                    <p><strong>Bergabung:</strong> {{ $user->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Transaksi --}}
    @if($transaksis->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-receipt"></i> Riwayat Transaksi ({{ $transaksis->total() }} transaksi)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status Pesanan</th>
                                <th>Tracking</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaksis as $transaksi)
                            <tr>
                                <td>
                                    <strong>#{{ $transaksi->id }}</strong>
                                </td>
                                <td>{{ $transaksi->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-success">
                                        Rp {{ number_format($transaksi->total, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaksi->metode_pembayaran == 'prepaid' ? 'primary' : 'warning' }}">
                                        {{ $transaksi->metode_pembayaran == 'prepaid' ? 'Prepaid' : 'Postpaid' }}
                                    </span>
                                </td>
                                <td>
                                    @if($transaksi->shippingOrder)
                                        @if($transaksi->shippingOrder->status === 'pending')
                                            <span class="badge bg-warning">Menunggu Konfirmasi</span>
                                        @elseif($transaksi->shippingOrder->status === 'shipped')
                                            <span class="badge bg-info">Sedang Dikirim</span>
                                        @elseif($transaksi->shippingOrder->status === 'delivered')
                                            <span class="badge bg-success">Sudah Sampai</span>
                                        @elseif($transaksi->shippingOrder->status === 'cancelled')
                                            <span class="badge bg-danger">Dibatalkan</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Belum Ada Shipping</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaksi->shippingOrder && $transaksi->shippingOrder->tracking_number)
                                        <code>{{ $transaksi->shippingOrder->tracking_number }}</code>
                                        @if($transaksi->shippingOrder->courier)
                                            <br><small class="text-muted">{{ $transaksi->shippingOrder->courier }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Tombol Download PDF --}}
                                    @if($transaksi->pdf_path)
                                        <a href="{{ route('customer.download.laporan', $transaksi->id) }}" 
                                           class="btn btn-sm btn-outline-primary me-1" 
                                           title="Download Laporan">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    @endif
                                    
                                    {{-- Tombol Cancel Order (hanya jika pending dan belum shipped) --}}
                                    @if($transaksi->shippingOrder && $transaksi->shippingOrder->status === 'pending')
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#cancelModal{{ $transaksi->id }}"
                                                title="Batalkan Pesanan">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @endif

                                    {{-- Modal Konfirmasi Cancel --}}
                                    @if($transaksi->shippingOrder && $transaksi->shippingOrder->status === 'pending')
                                    <div class="modal fade" id="cancelModal{{ $transaksi->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Batalkan Pesanan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Apakah Anda yakin ingin membatalkan pesanan <strong>#{{ $transaksi->id }}</strong>?</p>
                                                    <p class="text-warning">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                        Pesanan yang sudah dibatalkan tidak dapat dikembalikan.
                                                    </p>
                                                    <div class="alert alert-info">
                                                        <strong>Detail Pesanan:</strong><br>
                                                        Total: Rp {{ number_format($transaksi->total, 0, ',', '.') }}<br>
                                                        Tanggal: {{ $transaksi->created_at->format('d/m/Y H:i') }}<br>
                                                        Status: Menunggu Konfirmasi Admin
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak, Kembali</button>
                                                    <form action="{{ route('customer.cancel.order', $transaksi->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger">Ya, Batalkan Pesanan</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $transaksis->links() }}
                </div>
            </div>
        </div>
    @else
        {{-- Tampilan jika belum ada transaksi --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-receipt" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3">Belum Ada Transaksi</h4>
                <p class="text-muted">Anda belum pernah melakukan pembelian.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="bi bi-bag-plus"></i> Mulai Belanja
                </a>
            </div>
        </div>
    @endif

    {{-- Tombol Kembali --}}
    <div class="mt-4">
        <a href="{{ route('home') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
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