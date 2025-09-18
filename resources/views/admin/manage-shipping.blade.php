@extends('layouts.app')

@section('content')
{{-- Konten utama halaman kelola pengiriman --}}
<div class="container">
    {{-- Tombol kembali ke dashboard --}}
    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-12">
            <h1>Kelola Pengiriman</h1>
            <p class="text-muted">Shipping Order - Kelola pengiriman dan tracking pesanan customer</p>
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

    {{-- Menampilkan daftar error validasi jika ada --}}
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

    {{-- Statistik jumlah pengiriman berdasarkan status --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Pending</h5>
                    <h2>{{ $shippingOrders->where('status', 'pending')->count() }}</h2> {{-- Jumlah pengiriman berstatus pending --}}
                    <small>Belum dikirim</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Shipped</h5>
                    <h2>{{ $shippingOrders->where('status', 'shipped')->count() }}</h2> {{-- Jumlah pengiriman berstatus shipped --}}
                    <small>Dalam perjalanan</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Delivered</h5>
                    <h2>{{ $shippingOrders->where('status', 'delivered')->count() }}</h2> {{-- Jumlah pengiriman berstatus delivered --}}
                    <small>Sudah sampai</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Cancelled</h5>
                    <h2>{{ $shippingOrders->where('status', 'cancelled')->count() }}</h2> {{-- Jumlah pengiriman berstatus cancelled --}}
                    <small>Dibatalkan</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel daftar pengiriman --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-truck"></i> Daftar Pengiriman ({{ $shippingOrders->total() }} total) {{-- Total jumlah pengiriman --}}
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
                                <th>ID</th> {{-- Kolom ID pengiriman --}}
                                <th>Customer</th> {{-- Kolom data customer --}}
                                <th>Transaksi</th> {{-- Kolom data transaksi --}}
                                <th>No. Resi</th> {{-- Kolom nomor resi pengiriman --}}
                                <th>Kurir</th> {{-- Kolom nama kurir --}}
                                <th>Status</th> {{-- Kolom status pengiriman --}}
                                <th>Tanggal Kirim</th> {{-- Kolom tanggal pengiriman --}}
                                <th>Aksi</th> {{-- Kolom aksi (edit/view) --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shippingOrders as $shipping)
                            <tr>
                                <td>{{ $shipping->id }}</td> {{-- ID pengiriman --}}
                                <td>
                                    <strong>{{ $shipping->transaksi->user->name }}</strong><br> {{-- Nama customer --}}
                                    <small class="text-muted">{{ $shipping->transaksi->user->email }}</small> {{-- Email customer --}}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">ID: {{ $shipping->transaksi->id }}</span><br> {{-- ID transaksi --}}
                                    <small>Rp {{ number_format($shipping->transaksi->total, 0, ',', '.') }}</small><br> {{-- Total transaksi --}}
                                    <small class="text-muted">{{ $shipping->transaksi->created_at->format('d/m/Y') }}</small> {{-- Tanggal transaksi --}}
                                </td>
                                <td>
                                    <strong>{{ $shipping->tracking_number }}</strong> {{-- Nomor resi pengiriman --}}
                                </td>
                                <td>
                                    @if($shipping->courier)
                                        {{ $shipping->courier }} {{-- Nama kurir jika ada --}}
                                    @else
                                        <span class="text-muted">-</span> {{-- Tanda jika kurir belum dipilih --}}
                                    @endif
                                </td>
                                <td>
                                    @if($shipping->status === 'pending')
                                        <span class="badge bg-warning">Pending</span> {{-- Status pengiriman pending --}}
                                    @elseif($shipping->status === 'shipped')
                                        <span class="badge bg-info">Shipped</span> {{-- Status pengiriman shipped --}}
                                    @elseif($shipping->status === 'delivered')
                                        <span class="badge bg-success">Delivered</span> {{-- Status pengiriman delivered --}}
                                    @else
                                        <span class="badge bg-danger">Cancelled</span> {{-- Status pengiriman cancelled --}}
                                    @endif
                                </td>
                                <td>{{ $shipping->shipped_date ? $shipping->shipped_date->format('d/m/Y') : '-' }}</td> {{-- Tanggal pengiriman atau tanda jika belum ada --}}
                                <td>
                                    {{-- Tombol untuk membuka modal update status --}}
                                    <button type="button" class="btn btn-sm btn-warning me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#updateModal{{ $shipping->id }}"
                                            title="Update Status">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    {{-- Tombol untuk membuka modal detail pengiriman --}}
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewModal{{ $shipping->id }}"
                                            title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- Modal untuk mengupdate status pengiriman --}}
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
                                                        {{-- Pilihan status pengiriman --}}
                                                        <div class="mb-3">
                                                            <label for="status{{ $shipping->id }}" class="form-label">Status <span class="text-danger">*</span></label>
                                                            <select id="status{{ $shipping->id }}" name="status" class="form-control" required>
                                                                <option value="pending" {{ $shipping->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="shipped" {{ $shipping->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                                <option value="delivered" {{ $shipping->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                                <option value="cancelled" {{ $shipping->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                            </select>
                                                        </div>

                                                        {{-- Pilihan kurir pengiriman --}}
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
                                                                <option value="Express Courier" {{ $shipping->courier == 'Express Courier' ? 'selected' : '' }}>Express Courier</option>
                                                                <option value="COD Service" {{ $shipping->courier == 'COD Service' ? 'selected' : '' }}>COD Service</option>
                                                            </select>
                                                        </div>

                                                        {{-- Input tanggal pengiriman --}}
                                                        <div class="mb-3">
                                                            <label for="shipped_date{{ $shipping->id }}" class="form-label">Tanggal Kirim</label>
                                                            <input type="date" 
                                                                   id="shipped_date{{ $shipping->id }}" 
                                                                   name="shipped_date" 
                                                                   class="form-control" 
                                                                   value="{{ $shipping->shipped_date ? $shipping->shipped_date->format('Y-m-d') : '' }}">
                                                        </div>

                                                        {{-- Input tanggal sampai --}}
                                                        <div class="mb-3">
                                                            <label for="delivered_date{{ $shipping->id }}" class="form-label">Tanggal Sampai</label>
                                                            <input type="date" 
                                                                   id="delivered_date{{ $shipping->id }}" 
                                                                   name="delivered_date" 
                                                                   class="form-control" 
                                                                   value="{{ $shipping->delivered_date ? $shipping->delivered_date->format('Y-m-d') : '' }}">
                                                        </div>

                                                        {{-- Input catatan pengiriman --}}
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

                                    {{-- Modal untuk menampilkan detail pengiriman --}}
                                    <div class="modal fade" id="viewModal{{ $shipping->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Pengiriman #{{ $shipping->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        {{-- Informasi data customer --}}
                                                        <div class="col-md-6">
                                                            <h6>Informasi Customer</h6>
                                                            <table class="table table-sm table-borderless">
                                                                <tr><td><strong>Nama:</strong></td><td>{{ $shipping->transaksi->user->name }}</td></tr> {{-- Nama customer --}}
                                                                <tr><td><strong>Email:</strong></td><td>{{ $shipping->transaksi->user->email }}</td></tr> {{-- Email customer --}}
                                                                <tr><td><strong>Alamat:</strong></td><td>{{ $shipping->transaksi->user->address ?? '-' }}</td></tr> {{-- Alamat customer --}}
                                                                <tr><td><strong>Kota:</strong></td><td>{{ $shipping->transaksi->user->city ?? '-' }}</td></tr> {{-- Kota customer --}}
                                                                <tr><td><strong>No. HP:</strong></td><td>{{ $shipping->transaksi->user->contact_no ?? '-' }}</td></tr> {{-- Nomor telepon customer --}}
                                                            </table>
                                                        </div>

                                                        {{-- Informasi data transaksi --}}
                                                        <div class="col-md-6">
                                                            <h6>Informasi Transaksi</h6>
                                                            <table class="table table-sm table-borderless">
                                                                <tr><td><strong>ID Transaksi:</strong></td><td>{{ $shipping->transaksi->id }}</td></tr> {{-- ID transaksi --}}
                                                                <tr><td><strong>Total:</strong></td><td>Rp {{ number_format($shipping->transaksi->total, 0, ',', '.') }}</td></tr> {{-- Total transaksi --}}
                                                                <tr><td><strong>Pembayaran:</strong></td><td>{{ ucfirst($shipping->transaksi->metode_pembayaran) }}</td></tr> {{-- Metode pembayaran --}}
                                                                <tr><td><strong>Tanggal Order:</strong></td><td>{{ $shipping->transaksi->created_at->format('d/m/Y H:i') }}</td></tr> {{-- Tanggal order --}}
                                                            </table>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    <div class="row">
                                                        {{-- Informasi data pengiriman --}}
                                                        <div class="col-md-12">
                                                            <h6>Informasi Pengiriman</h6>
                                                            <table class="table table-sm">
                                                                <tr><td><strong>No. Resi:</strong></td><td>{{ $shipping->tracking_number }}</td></tr> {{-- Nomor resi pengiriman --}}
                                                                <tr><td><strong>Kurir:</strong></td><td>{{ $shipping->courier ?? '-' }}</td></tr> {{-- Nama kurir --}}
                                                                <tr><td><strong>Status:</strong></td>
                                                                    <td>
                                                                        @if($shipping->status === 'pending')
                                                                            <span class="badge bg-warning">Pending</span> {{-- Status pengiriman pending --}}
                                                                        @elseif($shipping->status === 'shipped')
                                                                            <span class="badge bg-info">Shipped</span> {{-- Status pengiriman shipped --}}
                                                                        @elseif($shipping->status === 'delivered')
                                                                            <span class="badge bg-success">Delivered</span> {{-- Status pengiriman delivered --}}
                                                                        @else
                                                                            <span class="badge bg-danger">Cancelled</span> {{-- Status pengiriman cancelled --}}
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                <tr><td><strong>Tanggal Kirim:</strong></td><td>{{ $shipping->shipped_date ? $shipping->shipped_date->format('d/m/Y') : '-' }}</td></tr> {{-- Tanggal pengiriman --}}
                                                                <tr><td><strong>Tanggal Sampai:</strong></td><td>{{ $shipping->delivered_date ? $shipping->delivered_date->format('d/m/Y') : '-' }}</td></tr> {{-- Tanggal sampai --}}
                                                                <tr><td><strong>Catatan:</strong></td><td>{{ $shipping->notes ?? '-' }}</td></tr> {{-- Catatan pengiriman --}}
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

                {{-- Tautan paginasi untuk daftar pengiriman --}}
                <div class="mt-3">
                    {{ $shippingOrders->links() }}
                </div>
            @else
                {{-- Tampilan jika tidak ada data pengiriman --}}
                <div class="text-center py-4">
                    <i class="bi bi-truck" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">Belum Ada Pengiriman</h4>
                    <p class="text-muted">Belum ada order yang perlu dikirim.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal untuk membuat shipping order baru --}}
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
                        {{-- Pilihan transaksi untuk shipping order --}}
                        <div class="mb-3">
                            <label for="transaksi_id" class="form-label">Transaksi <span class="text-danger">*</span></label>
                            <select id="transaksi_id" name="transaksi_id" class="form-control" required>
                                <option value="">Pilih Transaksi...</option>
                                {{-- Memuat transaksi yang belum memiliki shipping order --}}
                                @php
                                    $transaksis = \App\Models\Transaksi::with('user')->whereDoesntHave('shippingOrder')->get();
                                @endphp
                                @foreach($transaksis as $transaksi)
                                    <option value="{{ $transaksi->id }}">
                                        ID: {{ $transaksi->id }} - {{ $transaksi->user->name }} (Rp {{ number_format($transaksi->total, 0, ',', '.') }}) {{-- Opsi transaksi --}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Input nomor resi pengiriman --}}
                        <div class="mb-3">
                            <label for="tracking_number" class="form-label">Nomor Resi <span class="text-danger">*</span></label>
                            <input type="text" 
                                   id="tracking_number" 
                                   name="tracking_number" 
                                   class="form-control" 
                                   required 
                                   placeholder="Contoh: JNE123456789">
                        </div>

                        {{-- Pilihan kurir pengiriman --}}
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

                        {{-- Input catatan pengiriman --}}
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
</div>
@endsection

@push('scripts')
<script>
// Inisialisasi fungsi saat dokumen dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Menyembunyikan alert secara otomatis setelah 5 detik
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });

    // Mengatur tanggal otomatis berdasarkan status pengiriman
    const statusSelects = document.querySelectorAll('select[name="status"]');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const shippingId = this.id.replace('status', '');
            const shippedDateInput = document.getElementById('shipped_date' + shippingId);
            const deliveredDateInput = document.getElementById('delivered_date' + shippingId);
            
            // Mengatur tanggal kirim otomatis jika status shipped
            if (this.value === 'shipped' && !shippedDateInput.value) {
                shippedDateInput.value = new Date().toISOString().split('T')[0];
            }
            
            // Mengatur tanggal kirim dan sampai otomatis jika status delivered
            if (this.value === 'delivered') {
                if (!shippedDateInput.value) {
                    shippedDateInput.value = new Date(Date.now() - 86400000).toISOString().split('T')[0]; // Kemarin
                }
                if (!deliveredDateInput.value) {
                    deliveredDateInput.value = new Date().toISOString().split('T')[0]; // Hari ini
                }
            }
        });
    });
});
</script>
@endpush