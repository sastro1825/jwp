@extends('layouts.app')

@section('content')
{{-- Halaman Kelola Permohonan Toko --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Kelola Permohonan Toko</h1>
            <p class="text-muted">Approve/Reject Shop Creation Request - Review permohonan pembukaan toko dari customer</p>
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

    {{-- Statistik Permohonan Toko --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Pending</h5>
                    <h2>{{ $totalPending }}</h2>
                    <small>Menunggu review</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Approved</h5>
                    <h2>{{ $totalApproved }}</h2>
                    <small>Disetujui</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Rejected</h5>
                    <h2>{{ $totalRejected }}</h2>
                    <small>Ditolak</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Total</h5>
                    <h2>{{ $totalPending + $totalApproved + $totalRejected }}</h2>
                    <small>Semua permohonan</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Permohonan Toko --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-shop"></i> Daftar Permohonan Toko Pending
            </h5>
        </div>
        <div class="card-body">
            @if($tokoRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Nama Toko</th>
                                <th>Kategori Usaha</th>
                                <th>Tanggal Ajuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tokoRequests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>
                                    <strong>{{ $request->user->name }}</strong><br>
                                    <small class="text-muted">{{ $request->user->email }}</small>
                                </td>
                                <td>
                                    <strong>{{ $request->nama_toko }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($request->deskripsi_toko, 50) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ ucwords(str_replace('-', ' ', $request->kategori_usaha)) }}
                                    </span>
                                </td>
                                <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    {{-- Tombol View Detail --}}
                                    <button type="button" class="btn btn-sm btn-info me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailModal{{ $request->id }}"
                                            title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- Tombol Approve --}}
                                    <button type="button" class="btn btn-sm btn-success me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#approveModal{{ $request->id }}"
                                            title="Setujui Permohonan">
                                        <i class="bi bi-check-circle"></i>
                                    </button>

                                    {{-- Tombol Reject --}}
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal{{ $request->id }}"
                                            title="Tolak Permohonan">
                                        <i class="bi bi-x-circle"></i>
                                    </button>

                                    {{-- Modal Detail Permohonan --}}
                                    <div class="modal fade" id="detailModal{{ $request->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Permohonan Toko #{{ $request->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        {{-- Info Customer --}}
                                                        <div class="col-md-6">
                                                            <h6>Informasi Customer</h6>
                                                            <table class="table table-sm table-borderless">
                                                                <tr><td><strong>Nama:</strong></td><td>{{ $request->user->name }}</td></tr>
                                                                <tr><td><strong>Email:</strong></td><td>{{ $request->user->email }}</td></tr>
                                                                <tr><td><strong>Alamat:</strong></td><td>{{ $request->user->address ?? '-' }}</td></tr>
                                                                <tr><td><strong>Kota:</strong></td><td>{{ $request->user->city ?? '-' }}</td></tr>
                                                                <tr><td><strong>No. HP:</strong></td><td>{{ $request->user->contact_no ?? '-' }}</td></tr>
                                                            </table>
                                                        </div>

                                                        {{-- Info Toko --}}
                                                        <div class="col-md-6">
                                                            <h6>Informasi Toko</h6>
                                                            <table class="table table-sm table-borderless">
                                                                <tr><td><strong>Nama Toko:</strong></td><td>{{ $request->nama_toko }}</td></tr>
                                                                <tr><td><strong>Kategori:</strong></td><td>{{ ucwords(str_replace('-', ' ', $request->kategori_usaha)) }}</td></tr>
                                                                <tr><td><strong>No. Telepon:</strong></td><td>{{ $request->no_telepon ?? '-' }}</td></tr>
                                                                <tr><td><strong>Tanggal Ajuan:</strong></td><td>{{ $request->created_at->format('d/m/Y H:i') }}</td></tr>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    {{-- Alamat Toko --}}
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <h6>Alamat Toko</h6>
                                                            <div class="bg-light p-3 rounded">
                                                                {{ $request->alamat_toko }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    {{-- Deskripsi Toko --}}
                                                    @if($request->deskripsi_toko)
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <h6>Deskripsi Toko</h6>
                                                            <div class="bg-light p-3 rounded">
                                                                {{ $request->deskripsi_toko }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    @endif

                                                    {{-- Alasan Permohonan --}}
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <h6>Alasan Permohonan</h6>
                                                            <div class="bg-light p-3 rounded">
                                                                {{ $request->alasan_permohonan }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="button" class="btn btn-success" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#approveModal{{ $request->id }}">
                                                        <i class="bi bi-check-circle"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Modal Approve --}}
                                    <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Setujui Permohonan Toko</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.toko.approve', $request->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="alert alert-success">
                                                            <strong>Toko yang akan disetujui:</strong> {{ $request->nama_toko }}<br>
                                                            <strong>Pemilik:</strong> {{ $request->user->name }}
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="catatan_admin{{ $request->id }}" class="form-label">Catatan Admin (Opsional)</label>
                                                            <textarea id="catatan_admin{{ $request->id }}" 
                                                                      name="catatan_admin" 
                                                                      class="form-control" 
                                                                      rows="3" 
                                                                      placeholder="Berikan catatan untuk customer...">Selamat! Permohonan toko Anda telah disetujui. Silakan mulai mengelola toko Anda.</textarea>
                                                        </div>

                                                        <div class="alert alert-info">
                                                            <i class="bi bi-info-circle"></i>
                                                            <strong>Yang akan terjadi:</strong>
                                                            <ul class="mb-0 mt-2">
                                                                <li>Customer akan mendapat akses admin toko</li>
                                                                <li>Toko akan masuk ke database sistem</li>
                                                                <li>Customer dapat mulai mengelola produk</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-success">Ya, Setujui Toko</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Modal Reject --}}
                                    <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Tolak Permohonan Toko</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.toko.reject', $request->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <strong>Toko yang akan ditolak:</strong> {{ $request->nama_toko }}<br>
                                                            <strong>Pemilik:</strong> {{ $request->user->name }}
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="catatan_reject{{ $request->id }}" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                                            <textarea id="catatan_reject{{ $request->id }}" 
                                                                      name="catatan_admin" 
                                                                      class="form-control" 
                                                                      rows="4" 
                                                                      required
                                                                      placeholder="Jelaskan alasan penolakan permohonan..."></textarea>
                                                        </div>

                                                        <div class="alert alert-danger">
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                            Customer akan menerima notifikasi penolakan beserta alasan yang Anda berikan.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Ya, Tolak Permohonan</button>
                                                    </div>
                                                </form>
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
                    {{ $tokoRequests->links() }}
                </div>
            @else
                {{-- Tampilan jika tidak ada permohonan pending --}}
                <div class="text-center py-4">
                    <i class="bi bi-shop" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">Tidak Ada Permohonan Pending</h4>
                    <p class="text-muted">Saat ini tidak ada permohonan toko yang menunggu review.</p>
                </div>
            @endif
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