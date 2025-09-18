@extends('layouts.app')

{{-- Bagian konten utama --}}
@section('content')
{{-- Halaman Kelola Customer --}}
<div class="container">
    {{-- Tombol kembali ke dashboard --}}
    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    {{-- Judul dan deskripsi halaman --}}
    <div class="row">
        <div class="col-12">
            <h1>Kelola Customers</h1>
            <p class="text-muted">Manage Customer Database - Edit dan hapus data customer</p>
        </div>
    </div>

    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Notifikasi error --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tabel data customer --}}
    <div class="card">
        {{-- Header tabel dengan jumlah customer --}}
        <div class="card-header">
            <h5 class="mb-0">Daftar Customer (Total: {{ $jumlahCustomer }})</h5>
        </div>
        <div class="card-body">
            {{-- Cek apakah ada data customer --}}
            @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        {{-- Header tabel --}}
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Kota</th>
                                <th>No. HP</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Looping data customer --}}
                            @foreach($customers as $customer)
                            <tr>
                                <td>{{ $customer->id }}</td>
                                <td>
                                    <strong>{{ $customer->name }}</strong>
                                    {{-- Badge untuk role customer --}}
                                    @if($customer->role === 'customer')
                                        <span class="badge bg-primary ms-1">Customer</span>
                                    @endif
                                </td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->city ?? '-' }}</td>
                                <td>{{ $customer->contact_no ?? '-' }}</td>
                                <td>{{ $customer->created_at->format('d/m/Y') }}</td>
                                <td>
                                    {{-- Tombol edit customer --}}
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}" 
                                       class="btn btn-sm btn-warning me-1" title="Edit Customer">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    
                                    {{-- Tombol hapus customer --}}
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $customer->id }}"
                                            title="Hapus Customer">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                    
                                    {{-- Modal konfirmasi hapus --}}
                                    <div class="modal fade" id="deleteModal{{ $customer->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Konfirmasi Hapus Customer</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Apakah Anda yakin ingin menghapus customer <strong>{{ $customer->name }}</strong>?</p>
                                                    <p class="text-danger">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                        Semua data terkait customer ini (transaksi, keranjang, feedback) akan ikut terhapus!
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    {{-- Form untuk menghapus customer --}}
                                                    <form action="{{ route('admin.customers.delete', $customer->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                                    </form>
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

                {{-- Link paginasi --}}
                <div class="mt-3">
                    {{ $customers->links() }}
                </div>
            @else
                {{-- Tampilan jika tidak ada data customer --}}
                <div class="text-center py-4">
                    <i class="bi bi-people" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">Belum Ada Customer</h4>
                    <p class="text-muted">Belum ada customer yang terdaftar dalam sistem.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection