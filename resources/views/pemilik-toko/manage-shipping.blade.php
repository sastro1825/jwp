@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header dengan informasi toko --}}
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Kelola Pengiriman - {{ $toko ? $toko->nama : 'Toko' }}</h1> {{-- Judul halaman dengan nama toko atau default 'Toko' --}}
            <p class="text-muted">Monitor pengiriman khusus untuk pesanan Anda sebagai pemilik toko</p> {{-- Deskripsi halaman --}}
            
            {{-- Informasi khusus untuk pemilik toko --}}
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Info:</strong> Halaman ini menampilkan pengiriman untuk pesanan yang Anda buat sebagai pemilik toko, bukan pengiriman untuk toko Anda.
            </div>
        </div>
    </div>

    {{-- Menampilkan pesan sukses jika ada --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }} {{-- Pesan sukses dari sesi --}}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button> {{-- Tombol tutup alert --}}
        </div>
    @endif

    {{-- Menampilkan pesan error jika ada --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }} {{-- Pesan error dari sesi --}}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button> {{-- Tombol tutup alert --}}
        </div>
    @endif

    {{-- Statistik pengiriman dalam kartu --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3>{{ $shippingOrders->where('status', 'pending')->count() }}</h3> {{-- Jumlah pesanan dengan status pending --}}
                    <p class="mb-0">Pending</p> {{-- Label status pending --}}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $shippingOrders->where('status', 'shipped')->count() }}</h3> {{-- Jumlah pesanan dengan status dikirim --}}
                    <p class="mb-0">Dikirim</p> {{-- Label status dikirim --}}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $shippingOrders->where('status', 'delivered')->count() }}</h3> {{-- Jumlah pesanan dengan status sampai --}}
                    <p class="mb-0">Sampai</p> {{-- Label status sampai --}}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h3>{{ $shippingOrders->count() }}</h3> {{-- Total jumlah pesanan --}}
                    <p class="mb-0">Total</p> {{-- Label total pesanan --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel daftar pengiriman --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-truck"></i> Daftar Pengiriman {{-- Judul tabel pengiriman --}}
            </h5>
        </div>
        <div class="card-body">
            @if($shippingOrders->count() > 0) {{-- Cek apakah ada data pengiriman --}}
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th> {{-- Kolom ID pengiriman --}}
                                <th>Customer</th> {{-- Kolom nama customer --}}
                                <th>Transaksi</th> {{-- Kolom detail transaksi --}}
                                <th>No. Resi</th> {{-- Kolom nomor resi --}}
                                <th>Kurir</th> {{-- Kolom kurir pengiriman --}}
                                <th>Status</th> {{-- Kolom status pengiriman --}}
                                <th>Tanggal Kirim</th> {{-- Kolom tanggal pengiriman --}}
                                <th>Aksi</th> {{-- Kolom aksi (update, lihat detail) --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shippingOrders as $order) {{-- Looping data pengiriman --}}
                            <tr>
                                <td>{{ $order->id }}</td> {{-- ID pengiriman --}}
                                <td>
                                    <strong>{{ $order->transaksi->user->name ?? 'N/A' }}</strong><br> {{-- Nama customer atau N/A jika tidak ada --}}
                                    <small class="text-muted">{{ $order->transaksi->user->email ?? '' }}</small> {{-- Email customer --}}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">ID: {{ $order->transaksi->id }}</span><br> {{-- ID transaksi --}}
                                    <small>Rp {{ number_format($order->transaksi->total, 0, ',', '.') }}</small><br> {{-- Total transaksi dalam format rupiah --}}
                                    <small class="text-muted">{{ $order->transaksi->created_at->format('d/m/Y') }}</small> {{-- Tanggal transaksi --}}
                                </td>
                                <td>
                                    <strong>{{ $order->tracking_number }}</strong> {{-- Nomor resi pengiriman --}}
                                </td>
                                <td>
                                    {{ $order->courier ?? '-' }} {{-- Nama kurir atau tanda - jika tidak ada --}}
                                </td>
                                <td>
                                    @if($order->status === 'pending') {{-- Kondisi status pending --}}
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($order->status === 'shipped') {{-- Kondisi status shipped --}}
                                        <span class="badge bg-info">Shipped</span>
                                    @elseif($order->status === 'delivered') {{-- Kondisi status delivered --}}
                                        <span class="badge bg-success">Delivered</span>
                                    @else {{-- Kondisi status cancelled atau lainnya --}}
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>{{ $order->shipped_date ? $order->shipped_date->format('d/m/Y') : '-' }}</td> {{-- Tanggal kirim atau tanda - jika tidak ada --}}
                                <td>
                                    {{-- Tombol untuk membuka modal update status --}}
                                    <button type="button" class="btn btn-sm btn-warning me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#updateModal{{ $order->id }}"
                                            title="Update Status">
                                        <i class="bi bi-pencil-square"></i> {{-- Ikon edit --}}
                                    </button>

                                    {{-- Tombol untuk membuka modal detail pengiriman --}}
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewModal{{ $order->id }}"
                                            title="Lihat Detail">
                                        <i class="bi bi-eye"></i> {{-- Ikon lihat detail --}}
                                    </button>

                                    {{-- Modal untuk update status pengiriman --}}
                                    <div class="modal fade" id="updateModal{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Status Pengiriman #{{ $order->id }}</h5> {{-- Judul modal update status --}}
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button> {{-- Tombol tutup modal --}}
                                                </div>
                                                {{-- Form untuk update status pengiriman --}}
                                                <form action="{{ route('pemilik-toko.shipping.status', $order->id) }}" method="POST">
                                                    @csrf {{-- Token CSRF untuk keamanan form --}}
                                                    @method('PATCH') {{-- Method PATCH untuk update data --}}
                                                    <div class="modal-body">
                                                        {{-- Pilihan status pengiriman --}}
                                                        <div class="mb-3">
                                                            <label for="status{{ $order->id }}" class="form-label">Status</label>
                                                            <select id="status{{ $order->id }}" name="status" class="form-control" required>
                                                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option> {{-- Opsi status pending --}}
                                                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option> {{-- Opsi status shipped --}}
                                                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option> {{-- Opsi status delivered --}}
                                                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option> {{-- Opsi status cancelled --}}
                                                            </select>
                                                        </div>

                                                        {{-- Pilihan kurir pengiriman --}}
                                                        <div class="mb-3">
                                                            <label for="courier{{ $order->id }}" class="form-label">Kurir</label>
                                                            <select id="courier{{ $order->id }}" name="courier" class="form-control">
                                                                <option value="">Pilih Kurir...</option> {{-- Opsi default kurir --}}
                                                                <option value="JNE" {{ $order->courier == 'JNE' ? 'selected' : '' }}>JNE</option> {{-- Opsi kurir JNE --}}
                                                                <option value="TIKI" {{ $order->courier == 'TIKI' ? 'selected' : '' }}>TIKI</option> {{-- Opsi kurir TIKI --}}
                                                                <option value="POS" {{ $order->courier == 'POS' ? 'selected' : '' }}>POS Indonesia</option> {{-- Opsi kurir POS --}}
                                                                <option value="J&T" {{ $order->courier == 'J&T' ? 'selected' : '' }}>J&T Express</option> {{-- Opsi kurir J&T --}}
                                                                <option value="SiCepat" {{ $order->courier == 'SiCepat' ? 'selected' : '' }}>SiCepat</option> {{-- Opsi kurir SiCepat --}}
                                                            </select>
                                                        </div>

                                                        {{-- Input tanggal kirim --}}
                                                        <div class="mb-3">
                                                            <label for="shipped_date{{ $order->id }}" class="form-label">Tanggal Kirim</label>
                                                            <input type="date" 
                                                                   id="shipped_date{{ $order->id }}" 
                                                                   name="shipped_date" 
                                                                   class="form-control" 
                                                                   value="{{ $order->shipped_date ? $order->shipped_date->format('Y-m-d') : '' }}"> {{-- Input tanggal kirim dengan format Y-m-d --}}
                                                        </div>

                                                        {{-- Input tanggal sampai --}}
                                                        <div class="mb-3">
                                                            <label for="delivered_date{{ $order->id }}" class="form-label">Tanggal Sampai</label>
                                                            <input type="date" 
                                                                   id="delivered_date{{ $order->id }}" 
                                                                   name="delivered_date" 
                                                                   class="form-control" 
                                                                   value="{{ $order->delivered_date ? $order->delivered_date->format('Y-m-d') : '' }}"> {{-- Input tanggal sampai dengan format Y-m-d --}}
                                                        </div>

                                                        {{-- Input catatan pengiriman --}}
                                                        <div class="mb-3">
                                                            <label for="notes{{ $order->id }}" class="form-label">Catatan</label>
                                                            <textarea id="notes{{ $order->id }}" 
                                                                      name="notes" 
                                                                      class="form-control" 
                                                                      rows="3">{{ $order->notes }}</textarea> {{-- Textarea untuk catatan pengiriman --}}
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button> {{-- Tombol batal modal --}}
                                                        <button type="submit" class="btn btn-primary">Update Status</button> {{-- Tombol submit form update --}}
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Modal untuk melihat detail pengiriman --}}
                                    <div class="modal fade" id="viewModal{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Pengiriman #{{ $order->id }}</h5> {{-- Judul modal detail pengiriman --}}
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button> {{-- Tombol tutup modal --}}
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Informasi Customer</h6> {{-- Subjudul informasi customer --}}
                                                            <table class="table table-sm table-borderless">
                                                                <tr><td><strong>Nama:</strong></td><td>{{ $order->transaksi->user->name ?? 'N/A' }}</td></tr> {{-- Nama customer atau N/A jika tidak ada --}}
                                                                <tr><td><strong>Email:</strong></td><td>{{ $order->transaksi->user->email ?? 'N/A' }}</td></tr> {{-- Email customer atau N/A jika tidak ada --}}
                                                                <tr><td><strong>No. HP:</strong></td><td>{{ $order->transaksi->user->contact_no ?? '-' }}</td></tr> {{-- Nomor HP customer atau tanda - jika tidak ada --}}
                                                            </table>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Informasi Pengiriman</h6> {{-- Subjudul informasi pengiriman --}}
                                                            <table class="table table-sm table-borderless">
                                                                <tr><td><strong>No. Resi:</strong></td><td>{{ $order->tracking_number }}</td></tr> {{-- Nomor resi pengiriman --}}
                                                                <tr><td><strong>Status:</strong></td><td><span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'shipped' ? 'info' : ($order->status === 'delivered' ? 'success' : 'danger')) }}">{{ ucfirst($order->status) }}</span></td></tr> {{-- Status pengiriman dengan badge sesuai kondisi --}}
                                                                <tr><td><strong>Kurir:</strong></td><td>{{ $order->courier ?? '-' }}</td></tr> {{-- Nama kurir atau tanda - jika tidak ada --}}
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button> {{-- Tombol tutup modal detail --}}
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

                {{-- Menampilkan pagination untuk daftar pengiriman --}}
                <div class="mt-3">
                    {{ $shippingOrders->links() }} {{-- Link pagination dari Laravel --}}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-truck" style="font-size: 4rem; color: #ccc;"></i> {{-- Ikon truk untuk placeholder kosong --}}
                    <h4 class="mt-3">Belum Ada Pengiriman</h4> {{-- Pesan jika tidak ada pengiriman --}}
                    <p class="text-muted">Pengiriman akan muncul setelah ada transaksi yang perlu dikirim.</p> {{-- Deskripsi tambahan untuk placeholder kosong --}}
                </div>
            @endif
        </div>
    </div>

    {{-- Tombol navigasi ke dashboard dan area customer --}}
    <div class="mt-4">
        <a href="{{ route('pemilik-toko.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard {{-- Tombol kembali ke dashboard pemilik toko --}}
        </a>
        <a href="{{ route('customer.area') }}" class="btn btn-outline-primary ms-2">
            <i class="bi bi-shop"></i> Area Customer {{-- Tombol menuju area customer --}}
        </a>
    </div>
</div>

{{-- Modal placeholder untuk detail pengiriman --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pengiriman</h5> {{-- Judul modal placeholder detail pengiriman --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button> {{-- Tombol tutup modal placeholder --}}
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Detail akan dimuat via JavaScript --> {{-- Placeholder untuk konten detail yang dimuat melalui JavaScript --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button> {{-- Tombol tutup modal placeholder --}}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Fungsi untuk mengupdate status pengiriman dengan konfirmasi
    function updateStatus(orderId, newStatus) {
        if (confirm('Yakin update status pengiriman?')) { // Konfirmasi sebelum update status
            // Implementasi AJAX untuk update status (placeholder)
            alert('Fitur update status akan segera tersedia. Order ID: ' + orderId + ', Status: ' + newStatus); // Pesan placeholder untuk update status
        }
    }

    // Fungsi untuk menampilkan detail pengiriman di modal
    function showDetail(orderId) {
        // Mengisi konten modal dengan detail pengiriman
        document.getElementById('detailContent').innerHTML = '<p>Detail pengiriman untuk Order ID: ' + orderId + '</p>'; // Isi konten modal placeholder
        new bootstrap.Modal(document.getElementById('detailModal')).show(); // Menampilkan modal detail
    }
</script>
@endpush