@extends('layouts.app')

@section('content')
{{-- Halaman Kelola Shipping Orders - DIPERBAIKI --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Kelola Pengiriman</h1>
            <p class="text-muted">Shipping Order - Kelola pengiriman dan tracking pesanan customer</p>
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Statistik Shipping --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Pending</h5>
                    <h2>{{ $shippingOrders->where('status', 'pending')->count() }}</h2>
                    <small>Belum dikirim</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Shipped</h5>
                    <h2>{{ $shippingOrders->where('status', 'shipped')->count() }}</h2>
                    <small>Dalam perjalanan</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Delivered</h5>
                    <h2>{{ $shippingOrders->where('status', 'delivered')->count() }}</h2>
                    <small>Sudah sampai</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Cancelled</h5>
                    <h2>{{ $shippingOrders->where('status', 'cancelled')->count() }}</h2>
                    <small>Dibatalkan</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Shipping Orders --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-truck"></i> Daftar Pengiriman ({{ $shippingOrders->total() }} total)
            </h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createShippingModal">
                <i class="bi bi-plus-circle"></i> Buat Shipping Order
            </button>
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
                            @foreach($shippingOrders as $shipping)
                            <tr>
                                <td>{{ $shipping->id }}</td>
                                <td>
                                    <strong>{{ $shipping->transaksi->user->name }}</strong><br>
                                    <small class="text-muted">{{ $shipping->transaksi->user->email }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">ID: {{ $shipping->transaksi->id }}</span><br>
                                    <small>Rp {{ number_format($shipping->transaksi->total, 0, ',', '.') }}</small><br>
                                    <small class="text-muted">{{ $shipping->transaksi->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $shipping->tracking_number }}</strong>
                                </td>
                                <td>{{ $shipping->courier ?? '-' }}</td>
                                <td>
                                    @if($shipping->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($shipping->status === 'shipped')
                                        <span class="badge bg-info">Shipped</span>
                                    @elseif($shipping->status === 'delivered')
                                        <span class="badge bg-success">Delivered</span>
                                    @else
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>{{ $shipping->shipped_date ? $shipping->shipped_date->format('d/m/Y') : '-' }}</td>
                                <td>
                                    {{-- Tombol Update Status --}}
                                    <button type="button" class="btn btn-sm btn-warning me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#updateModal{{ $shipping->id }}"
                                            title="Update Status">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    {{-- Tombol View Detail --}}
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewModal{{ $shipping->id }}"
                                            title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- Modal Update Status - DIPERBAIKI --}}
                                    <div class="modal fade" id="updateModal{{ $shipping->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Status Pengiriman #{{ $shipping->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.shipping.status', $shipping->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-body">
                                                        {{-- Status --}}
                                                        <div class="mb-3">
                                                            <label for="status{{ $shipping->id }}" class="form-label">Status <span class="text-danger">*</span></label>
                                                            <select id="status{{ $shipping->id }}" name="status" class="form-control" required>
                                                                <option value="pending" {{ $shipping->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="shipped" {{ $shipping->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                                <option value="delivered" {{ $shipping->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                                <option value="cancelled" {{ $shipping->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                            </select>
                                                        </div>

                                                        {{-- Kurir --}}
                                                        <div class="mb-3">
                                                            <label for="courier{{ $shipping->id }}" class="form-label">Kurir</label>
                                                            <select id="courier{{ $shipping->id }}" name="courier" class="form-control">
                                                                <option value="">Pilih Kurir...</option>
                                                                <option value="JNE" {{ $shipping->courier == 'JNE' ? 'selected' : '' }}>JNE</option>
                                                                <option value="TIKI" {{ $shipping->courier == 'TIKI' ? 'selected' : '' }}>TIKI</option>
                                                                <option value="POS" {{ $shipping->courier == 'POS' ? 'selected' : '' }}>POS Indonesia</option>
                                                                <option value="J&T" {{ $shipping->courier == 'J&T' ? 'selected' : '' }}>J&T Express</option>
                                                                <option value="SiCepat" {{ $shipping->courier == 'SiCepat' ? 'selected' : '' }}>SiCepat</option>
                                                                <option value="Anteraja" {{ $shipping->courier == 'Anteraja' ? 'selected' : '' }}>Anteraja</option>
                                                            </select>
                                                        </div>

                                                        {{-- Tanggal Kirim --}}
                                                        <div class="mb-3">
                                                            <label for="shipped_date{{ $shipping->id }}" class="form-label">Tanggal Kirim</label>
                                                            <input type="date" 
                                                                   id="shipped_date{{ $shipping->id }}" 
                                                                   name="shipped_date" 
                                                                   class="form-control" 
                                                                   value="{{ $shipping->shipped_date ? $shipping->shipped_date->format('Y-m-d') : '' }}">
                                                        </div>

                                                        {{-- Tanggal Sampai --}}
                                                        <div class="mb-3">
                                                            <label for="delivered_date{{ $shipping->id }}" class="form-label">Tanggal Sampai</label>
                                                            <input type="date" 
                                                                   id="delivered_date{{ $shipping->id }}" 
                                                                   name="delivered_date" 
                                                                   class="form-control" 
                                                                   value="{{ $shipping->delivered_date ? $shipping->delivered_date->format('Y-m-d') : '' }}">
                                                        </div>

                                                        {{-- Catatan --}}
                                                        <div class="mb-3">
                                                            <label for="notes{{ $shipping->id }}" class="form-label">Catatan</label>
                                                            <textarea id="notes{{ $shipping->id }}" 
                                                                      name="notes" 
                                                                      class="form-control" 
                                                                      rows="3">{{ $shipping->notes }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerHTML='<i class=\'bi bi-hourglass-split\'></i> Updating...'; this.form.submit();">
                                                            Update Status
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Modal View Detail --}}
                                    <div class="modal fade" id="viewModal{{ $shipping->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Pengiriman #{{ $shipping->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        {{-- Info Customer --}}
                                                        <div class="col-md-6">
                                                            <h6>Informasi Customer</h6>
                                                            <table class="table table-sm table-borderless">
                                                                <tr><td><strong>Nama:</strong></td><td>{{ $shipping->transaksi->user->name }}</td></tr>
                                                                <tr><td><strong>Email:</strong></td><td>{{ $shipping->transaksi->user->email }}</td></tr>
                                                                <tr><td><strong>Alamat:</strong></td><td>{{ $shipping->transaksi->user->address ?? '-' }}</td></tr>
                                                                <tr><td><strong>Kota:</strong></td><td>{{ $shipping->transaksi->user->city ?? '-' }}</td></tr>
                                                                <tr><td><strong>No. HP:</strong></td><td>{{ $shipping->transaksi->user->contact_no ?? '-' }}</td></tr>
                                                            </table>
                                                        </div>

                                                        {{-- Info Transaksi --}}
                                                        <div class="col-md-6">
                                                            <h6>Informasi Transaksi</h6>
                                                            <table class="table table-sm table-borderless">
                                                                <tr><td><strong>ID Transaksi:</strong></td><td>{{ $shipping->transaksi->id }}</td></tr>
                                                                <tr><td><strong>Total:</strong></td><td>Rp {{ number_format($shipping->transaksi->total, 0, ',', '.') }}</td></tr>
                                                                <tr><td><strong>Pembayaran:</strong></td><td>{{ ucfirst($shipping->transaksi->metode_pembayaran) }}</td></tr>
                                                                <tr><td><strong>Tanggal Order:</strong></td><td>{{ $shipping->transaksi->created_at->format('d/m/Y H:i') }}</td></tr>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    <div class="row">
                                                        {{-- Info Shipping --}}
                                                        <div class="col-md-12">
                                                            <h6>Informasi Pengiriman</h6>
                                                            <table class="table table-sm">
                                                                <tr><td><strong>No. Resi:</strong></td><td>{{ $shipping->tracking_number }}</td></tr>
                                                                <tr><td><strong>Kurir:</strong></td><td>{{ $shipping->courier ?? '-' }}</td></tr>
                                                                <tr><td><strong>Status:</strong></td>
                                                                    <td>
                                                                        @if($shipping->status === 'pending')
                                                                            <span class="badge bg-warning">Pending</span>
                                                                        @elseif($shipping->status === 'shipped')
                                                                            <span class="badge bg-info">Shipped</span>
                                                                        @elseif($shipping->status === 'delivered')
                                                                            <span class="badge bg-success">Delivered</span>
                                                                        @else
                                                                            <span class="badge bg-danger">Cancelled</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                <tr><td><strong>Tanggal Kirim:</strong></td><td>{{ $shipping->shipped_date ? $shipping->shipped_date->format('d/m/Y') : '-' }}</td></tr>
                                                                <tr><td><strong>Tanggal Sampai:</strong></td><td>{{ $shipping->delivered_date ? $shipping->delivered_date->format('d/m/Y') : '-' }}</td></tr>
                                                                <tr><td><strong>Catatan:</strong></td><td>{{ $shipping->notes ?? '-' }}</td></tr>
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

                {{-- Pagination Links --}}
                <div class="mt-3">
                    {{ $shippingOrders->links() }}
                </div>
            @else
                {{-- Tampilan jika tidak ada shipping order --}}
                <div class="text-center py-4">
                    <i class="bi bi-truck" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">Belum Ada Pengiriman</h4>
                    <p class="text-muted">Belum ada order yang perlu dikirim.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Create Shipping Order --}}
    <div class="modal fade" id="createShippingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Shipping Order Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.shipping.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        {{-- Pilih Transaksi --}}
                        <div class="mb-3">
                            <label for="transaksi_id" class="form-label">Transaksi <span class="text-danger">*</span></label>
                            <select id="transaksi_id" name="transaksi_id" class="form-control" required>
                                <option value="">Pilih Transaksi...</option>
                                {{-- Tampilkan transaksi yang belum ada shipping order --}}
                                @php
                                    $transaksis = \App\Models\Transaksi::with('user')->whereDoesntHave('shippingOrder')->get();
                                @endphp
                                @foreach($transaksis as $transaksi)
                                    <option value="{{ $transaksi->id }}">
                                        ID: {{ $transaksi->id }} - {{ $transaksi->user->name }} (Rp {{ number_format($transaksi->total, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- No. Resi --}}
                        <div class="mb-3">
                            <label for="tracking_number" class="form-label">Nomor Resi <span class="text-danger">*</span></label>
                            <input type="text" 
                                   id="tracking_number" 
                                   name="tracking_number" 
                                   class="form-control" 
                                   required 
                                   placeholder="Contoh: JNE123456789">
                        </div>

                        {{-- Kurir --}}
                        <div class="mb-3">
                            <label for="courier" class="form-label">Kurir <span class="text-danger">*</span></label>
                            <select id="courier" name="courier" class="form-control" required>
                                <option value="">Pilih Kurir...</option>
                                <option value="JNE">JNE</option>
                                <option value="TIKI">TIKI</option>
                                <option value="POS">POS Indonesia</option>
                                <option value="J&T">J&T Express</option>
                                <option value="SiCepat">SiCepat</option>
                                <option value="Anteraja">Anteraja</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea id="notes" 
                                      name="notes" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Catatan tambahan untuk pengiriman..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Buat Shipping Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tombol Kembali ke Dashboard --}}
    <div class="mt-3">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });

    // Auto-set today's date for shipped status
    const statusSelects = document.querySelectorAll('select[name="status"]');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const shippingId = this.id.replace('status', '');
            const shippedDateInput = document.getElementById('shipped_date' + shippingId);
            const deliveredDateInput = document.getElementById('delivered_date' + shippingId);
            
            if (this.value === 'shipped' && !shippedDateInput.value) {
                shippedDateInput.value = new Date().toISOString().split('T')[0];
            }
            
            if (this.value === 'delivered') {
                if (!shippedDateInput.value) {
                    shippedDateInput.value = new Date(Date.now() - 86400000).toISOString().split('T')[0]; // Yesterday
                }
                if (!deliveredDateInput.value) {
                    deliveredDateInput.value = new Date().toISOString().split('T')[0]; // Today
                }
            }
        });
    });
});
</script>
@endpush