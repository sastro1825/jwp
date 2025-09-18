@extends('layouts.app')

{{-- Bagian konten utama --}}
@section('content')
{{-- Kontainer untuk status permohonan toko customer --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            {{-- Judul halaman --}}
            <h1 class="mb-4">Status Permohonan Toko</h1>
            {{-- Deskripsi halaman --}}
            <p class="text-muted">Pantau status permohonan pembukaan toko Anda</p>
        </div>
    </div>

    {{-- Pesan notifikasi sukses jika ada --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            {{-- Tombol untuk menutup alert --}}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Daftar permohonan toko jika ada data --}}
    @if($tokoRequests->count() > 0)
        <div class="card">
            <div class="card-header">
                {{-- Judul kartu dengan jumlah permohonan --}}
                <h5 class="mb-0">
                    <i class="bi bi-shop"></i> Riwayat Permohonan Toko ({{ $tokoRequests->total() }} permohonan)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    {{-- Tabel untuk menampilkan daftar permohonan --}}
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                {{-- Kolom header tabel --}}
                                <th>ID</th>
                                <th>Nama Toko</th>
                                <th>Kategori Usaha</th>
                                <th>Tanggal Ajuan</th>
                                <th>Status</th>
                                <th>Catatan Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Looping untuk setiap permohonan toko --}}
                            @foreach($tokoRequests as $request)
                            <tr>
                                <td><strong>#{{ $request->id }}</strong></td>
                                <td>
                                    {{-- Nama toko dan deskripsi singkat --}}
                                    <strong>{{ $request->nama_toko }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($request->deskripsi_toko, 50) }}</small>
                                </td>
                                <td>
                                    {{-- Menampilkan kategori usaha dengan badge --}}
                                    <span class="badge bg-info">
                                        {{ ucwords(str_replace('-', ' ', $request->kategori_usaha)) }}
                                    </span>
                                </td>
                                <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    {{-- Menampilkan status permohonan dengan badge sesuai kondisi --}}
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($request->status === 'approved')
                                        <span class="badge bg-success">Disetujui</span>
                                    @else
                                        <span class="badge bg-danger">Ditolak</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Tombol untuk melihat catatan admin jika ada --}}
                                    @if($request->catatan_admin)
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#catatanModal{{ $request->id }}">
                                            <i class="bi bi-eye"></i> Lihat
                                        </button>

                                        {{-- Modal untuk menampilkan catatan admin --}}
                                        <div class="modal fade" id="catatanModal{{ $request->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Catatan Admin</h5>
                                                        {{-- Tombol untuk menutup modal --}}
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        {{-- Menampilkan status permohonan di modal --}}
                                                        <div class="alert alert-{{ $request->status === 'approved' ? 'success' : 'warning' }}">
                                                            <strong>Status:</strong> 
                                                            {{ $request->status === 'approved' ? 'Disetujui' : ($request->status === 'rejected' ? 'Ditolak' : 'Pending') }}
                                                        </div>
                                                        <p><strong>Catatan dari Admin:</strong></p>
                                                        {{-- Menampilkan catatan admin dalam kotak berwarna --}}
                                                        <div class="bg-light p-3 rounded">
                                                            {{ $request->catatan_admin }}
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        {{-- Tombol untuk menutup modal --}}
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

                {{-- Navigasi paginasi untuk daftar permohonan --}}
                <div class="mt-3">
                    {{ $tokoRequests->links() }}
                </div>
            </div>
        </div>
    @else
        {{-- Tampilan jika tidak ada permohonan toko --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-shop" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3">Belum Ada Permohonan Toko</h4>
                <p class="text-muted">Anda belum pernah mengajukan permohonan pembukaan toko.</p>
                {{-- Tombol untuk mengajukan permohonan baru --}}
                <a href="{{ route('customer.toko.request') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Ajukan Permohonan Toko
                </a>
            </div>
        </div>
    @endif

    {{-- Tombol navigasi kembali dan ajukan permohonan baru --}}
    <div class="mt-4">
        {{-- Tombol kembali ke beranda --}}
        <a href="{{ route('home') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>
        {{-- Tombol untuk ajukan permohonan baru jika tidak ada permohonan pending atau disetujui --}}
        @if($tokoRequests->where('status', 'pending')->isEmpty() && $tokoRequests->where('status', 'approved')->isEmpty())
            <a href="{{ route('customer.toko.request') }}" class="btn btn-primary ms-2">
                <i class="bi bi-plus-circle"></i> Ajukan Permohonan Baru
            </a>
        @endif
    </div>
</div>
@endsection