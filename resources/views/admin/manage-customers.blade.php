@extends('layouts.app')

@section('content')
{{-- Halaman Kelola Customer --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Kelola Customers</h1>
            <p class="text-muted">Manage Customer Database - Edit dan hapus data customer</p>
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

    {{-- Table Customer Data --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Customer (Total: {{ $jumlahCustomer }})</h5>
        </div>
        <div class="card-body">
            @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
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
                            @foreach($customers as $customer)
                            <tr>
                                <td>{{ $customer->id }}</td>
                                <td>
                                    <strong>{{ $customer->name }}</strong>
                                    @if($customer->role === 'customer')
                                        <span class="badge bg-primary ms-1">Customer</span>
                                    @endif
                                </td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->city ?? '-' }}</td>
                                <td>{{ $customer->contact_no ?? '-' }}</td>
                                <td>{{ $customer->created_at->format('d/m/Y') }}</td>
                                <td>
                                    {{-- Tombol Edit Customer --}}
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}" 
                                       class="btn btn-sm btn-warning me-1" title="Edit Customer">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    
                                    {{-- Tombol Hapus Customer --}}
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $customer->id }}"
                                            title="Hapus Customer">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                    
                                    {{-- Modal Konfirmasi Hapus --}}
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

                {{-- Pagination Links --}}
                <div class="mt-3">
                    {{ $customers->links() }}
                </div>
            @else
                {{-- Tampilan jika tidak ada customer --}}
                <div class="text-center py-4">
                    <i class="bi bi-people" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">Belum Ada Customer</h4>
                    <p class="text-muted">Belum ada customer yang terdaftar dalam sistem.</p>
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