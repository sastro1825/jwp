@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header dengan info toko --}}
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Kelola Pengiriman - {{ $toko ? $toko->nama : 'Toko' }}</h1>
            <p class="text-muted">Monitor pengiriman khusus untuk pesanan Anda sebagai pemilik toko</p>
            
            {{-- Info khusus pemilik toko --}}
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Info:</strong> Halaman ini menampilkan pengiriman untuk pesanan yang Anda buat sebagai pemilik toko, 
                bukan pengiriman untuk toko Anda.
            </div>
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

    {{-- Tabel Shipping Orders sesuai format admin --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-truck"></i> Daftar Pengiriman
            </h5>
        </div>
        <div class="card-body">
            @if($shippingOrders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Transaksi</th>
                                <th>No. Resi</th>
                                <th>Kurir</th>
                                <th>Status</th>
                                <th>Tanggal Kirim</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shippingOrders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>
                                    <strong>{{ $order->transaksi->user->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $order->transaksi->user->email ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">ID: {{ $order->transaksi->id }}</span><br>
                                    <small>Rp {{ number_format($order->transaksi->total, 0, ',', '.') }}</small><br>
                                    <small class="text-muted">{{ $order->transaksi->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $order->tracking_number }}</strong>
                                </td>
                                <td>
                                    {{ $order->courier ?? '-' }}
                                </td>
                                <td>
                                    @if($order->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($order->status === 'shipped')
                                        <span class="badge bg-info">Shipped</span>
                                    @elseif($order->status === 'delivered')
                                        <span class="badge bg-success">Delivered</span>
                                    @else
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>{{ $order->shipped_date ? $order->shipped_date->format('d/m/Y') : '-' }}</td>
                                <td>
                                    {{-- Tombol Update Status --}}
                                    <button type="button" class="btn btn-sm btn-warning me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#updateModal{{ $order->id }}"
                                            title="Update Status">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    {{-- Tombol View Detail --}}
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewModal{{ $order->id }}"
                                            title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- Modal Update Status --}}
                                    <div class="modal fade" id="updateModal{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Status Pengiriman #{{ $order->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                {{-- Form update status - PERBAIKI ACTION --}}
                                                <form action="{{ route('pemilik-toko.shipping.status', $order->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-body">
                                                        {{-- Status --}}
                                                        <div class="mb-3">
                                                            <label for="status{{ $order->id }}" class="form-label">Status</label>
                                                            <select id="status{{ $order->id }}" name="status" class="form-control" required>
                                                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                            </select>
                                                        </div>

                                                        {{-- Kurir --}}
                                                        <div class="mb-3">
                                                            <label for="courier{{ $order->id }}" class="form-label">Kurir</label>
                                                            <select id="courier{{ $order->id }}" name="courier" class="form-control">
                                                                <option value="">Pilih Kurir...</option>
                                                                <option value="JNE" {{ $order->courier == 'JNE' ? 'selected' : '' }}>JNE</option>
                                                                <option value="TIKI" {{ $order->courier == 'TIKI' ? 'selected' : '' }}>TIKI</option>
                                                                <option value="POS" {{ $order->courier == 'POS' ? 'selected' : '' }}>POS Indonesia</option>
                                                                <option value="J&T" {{ $order->courier == 'J&T' ? 'selected' : '' }}>J&T Express</option>
                                                                <option value="SiCepat" {{ $order->courier == 'SiCepat' ? 'selected' : '' }}>SiCepat</option>
                                                            </select>
                                                        </div>

                                                        {{-- Tanggal Kirim --}}
                                                        <div class="mb-3">
                                                            <label for="shipped_date{{ $order->id }}" class="form-label">Tanggal Kirim</label>
                                                            <input type="date" 
                                                                   id="shipped_date{{ $order->id }}" 
                                                                   name="shipped_date" 
                                                                   class="form-control" 
                                                                   value="{{ $order->shipped_date ? $order->shipped_date->format('Y-m-d') : '' }}">
                                                        </div>

                                                        {{-- Tanggal Sampai --}}
                                                        <div class="mb-3">
                                                            <label for="delivered_date{{ $order->id }}" class="form-label">Tanggal Sampai</label>
                                                            <input type="date" 
                                                                   id="delivered_date{{ $order->id }}" 
                                                                   name="delivered_date" 
                                                                   class="form-control" 
                                                                   value="{{ $order->delivered_date ? $order->delivered_date->format('Y-m-d') : '' }}">
                                                        </div>

                                                        {{-- Catatan --}}
                                                        <div class="mb-3">
                                                            <label for="notes{{ $order->id }}" class="form-label">Catatan</label>
                                                            <textarea id="notes{{ $order->id }}" 
                                                                      name="notes" 
                                                                      class="form-control" 
                                                                      rows="3">{{ $order->notes }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Modal View Detail --}}
                                    <div class="modal fade" id="viewModal{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Pengiriman #{{ $order->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Informasi Customer</h6>
                                                            <table class="table table-sm table-borderless">
                                                                <tr><td><strong>Nama:</strong></td><td>{{ $order->transaksi->user->name ?? 'N/A' }}</td></tr>
                                                                <tr><td><strong>Email:</strong></td><td>{{ $order->transaksi->user->email ?? 'N/A' }}</td></tr>
                                                                <tr><td><strong>No. HP:</strong></td><td>{{ $order->transaksi->user->contact_no ?? '-' }}</td></tr>
                                                            </table>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Informasi Pengiriman</h6>
                                                            <table class="table table-sm table-borderless">
                                                                <tr><td><strong>No. Resi:</strong></td><td>{{ $order->tracking_number }}</td></tr>
                                                                <tr><td><strong>Status:</strong></td><td><span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'shipped' ? 'info' : ($order->status === 'delivered' ? 'success' : 'danger')) }}">{{ ucfirst($order->status) }}</span></td></tr>
                                                                <tr><td><strong>Kurir:</strong></td><td>{{ $order->courier ?? '-' }}</td></tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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