@extends('layouts.app')

@section('content')
{{-- Status Permohonan Toko Customer --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Status Permohonan Toko</h1>
            <p class="text-muted">Pantau status permohonan pembukaan toko Anda</p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Daftar Permohonan Toko --}}
    @if($tokoRequests->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-shop"></i> Riwayat Permohonan Toko ({{ $tokoRequests->total() }} permohonan)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nama Toko</th>
                                <th>Kategori Usaha</th>
                                <th>Tanggal Ajuan</th>
                                <th>Status</th>
                                <th>Catatan Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tokoRequests as $request)
                            <tr>
                                <td><strong>#{{ $request->id }}</strong></td>
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
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($request->status === 'approved')
                                        <span class="badge bg-success">Disetujui</span>
                                    @else
                                        <span class="badge bg-danger">Ditolak</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->catatan_admin)
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#catatanModal{{ $request->id }}">
                                            <i class="bi bi-eye"></i> Lihat
                                        </button>

                                        {{-- Modal Catatan Admin --}}
                                        <div class="modal fade" id="catatanModal{{ $request->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Catatan Admin</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-{{ $request->status === 'approved' ? 'success' : 'warning' }}">
                                                            <strong>Status:</strong> 
                                                            {{ $request->status === 'approved' ? 'Disetujui' : ($request->status === 'rejected' ? 'Ditolak' : 'Pending') }}
                                                        </div>
                                                        <p><strong>Catatan dari Admin:</strong></p>
                                                        <div class="bg-light p-3 rounded">
                                                            {{ $request->catatan_admin }}
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $tokoRequests->links() }}
                </div>
            </div>
        </div>
    @else
        {{-- Tampilan jika belum ada permohonan --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-shop" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3">Belum Ada Permohonan Toko</h4>
                <p class="text-muted">Anda belum pernah mengajukan permohonan pembukaan toko.</p>
                <a href="{{ route('customer.toko.request') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Ajukan Permohonan Toko
                </a>
            </div>
        </div>
    @endif

    {{-- Tombol Kembali dan Ajukan Baru --}}
    <div class="mt-4">
        <a href="{{ route('home') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>
        @if($tokoRequests->where('status', 'pending')->isEmpty() && $tokoRequests->where('status', 'approved')->isEmpty())
            <a href="{{ route('customer.toko.request') }}" class="btn btn-primary ms-2">
                <i class="bi bi-plus-circle"></i> Ajukan Permohonan Baru
            </a>
        @endif
    </div>
</div>
@endsection