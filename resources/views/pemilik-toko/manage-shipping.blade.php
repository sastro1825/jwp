@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Kelola Pengiriman - {{ $toko ? $toko->nama : 'Toko' }}</h1>
            <p class="text-muted">Monitor dan kelola pengiriman produk untuk toko Anda</p>
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

    {{-- Statistik Pengiriman --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3>{{ $shippingOrders->where('status', 'pending')->count() }}</h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $shippingOrders->where('status', 'shipped')->count() }}</h3>
                    <p class="mb-0">Dikirim</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $shippingOrders->where('status', 'delivered')->count() }}</h3>
                    <p class="mb-0">Sampai</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h3>{{ $shippingOrders->count() }}</h3>
                    <p class="mb-0">Total</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Shipping Orders --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-truck"></i> Daftar Pengiriman
            </h5>
        </div>
        <div class="card-body">
            @if($shippingOrders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tracking #</th>
                                <th>Customer</th>
                                <th>Transaksi</th>
                                <th>Alamat</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shippingOrders as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->tracking_number }}</strong>
                                </td>
                                <td>
                                    {{ $order->transaksi->user->name ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $order->transaksi->user->email ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">#{{ $order->transaksi_id }}</span><br>
                                    <small class="text-success">Rp {{ number_format($order->transaksi->total ?? 0, 0, ',', '.') }}</small>
                                </td>
                                <td>
                                    <small>{{ Str::limit($order->shipping_address, 50) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $order->shipping_method }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'shipped' => 'info', 
                                            'delivered' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $order->created_at->format('d/m/Y') }}<br>
                                    <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($order->status === 'pending')
                                        <button class="btn btn-sm btn-success" onclick="updateStatus({{ $order->id }}, 'shipped')">
                                            <i class="bi bi-truck"></i> Kirim
                                        </button>
                                    @elseif($order->status === 'shipped')
                                        <button class="btn btn-sm btn-primary" onclick="updateStatus({{ $order->id }}, 'delivered')">
                                            <i class="bi bi-check-circle"></i> Sampai
                                        </button>
                                    @endif
                                    
                                    <button class="btn btn-sm btn-outline-info" onclick="showDetail({{ $order->id }})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $shippingOrders->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-truck" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">Belum Ada Pengiriman</h4>
                    <p class="text-muted">Pengiriman akan muncul setelah ada transaksi yang perlu dikirim.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Navigation --}}
    <div class="mt-4">
        <a href="{{ route('pemilik-toko.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
        <a href="{{ route('customer.area') }}" class="btn btn-outline-primary ms-2">
            <i class="bi bi-shop"></i> Area Customer
        </a>
    </div>
</div>

{{-- Modal Detail (placeholder) --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Detail akan dimuat via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateStatus(orderId, newStatus) {
    if (confirm('Yakin update status pengiriman?')) {
        // Implementasi AJAX untuk update status
        alert('Fitur update status akan segera tersedia. Order ID: ' + orderId + ', Status: ' + newStatus);
    }
}

function showDetail(orderId) {
    // Implementasi show detail
    document.getElementById('detailContent').innerHTML = '<p>Detail pengiriman untuk Order ID: ' + orderId + '</p>';
    new bootstrap.Modal(document.getElementById('detailModal')).show();
}
</script>
@endpush