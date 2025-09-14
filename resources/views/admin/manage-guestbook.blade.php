@extends('layouts.app')

@section('content')
{{-- Halaman Kelola Guest Book --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Kelola Guest Book</h1>
            <p class="text-muted">View/Delete Guest Book Entries - Moderasi feedback dari customer</p>
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

    {{-- Statistik Feedback --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Pending</h5>
                    <h2>{{ $feedbacks->where('status', 'pending')->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Approved</h5>
                    <h2>{{ $feedbacks->where('status', 'approved')->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Rejected</h5>
                    <h2>{{ $feedbacks->where('status', 'rejected')->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Total</h5>
                    <h2>{{ $feedbacks->total() }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Guest Book Entries --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-chat-left-text"></i> Daftar Feedback Customer
            </h5>
        </div>
        <div class="card-body">
            @if($feedbacks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Pesan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feedbacks as $feedback)
                            <tr>
                                <td>{{ $feedback->id }}</td>
                                <td>
                                    <strong>{{ $feedback->name }}</strong><br>
                                    <small class="text-muted">{{ $feedback->email }}</small>
                                </td>
                                <td>
                                    <div style="max-width: 300px;">
                                        {{ Str::limit($feedback->message, 100) }}
                                        @if(strlen($feedback->message) > 100)
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#viewModal{{ $feedback->id }}" class="text-primary">
                                                Baca selengkapnya...
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($feedback->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($feedback->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $feedback->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    {{-- Tombol View Detail --}}
                                    <button type="button" class="btn btn-sm btn-info me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewModal{{ $feedback->id }}"
                                            title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- Tombol Approve (jika pending) --}}
                                    @if($feedback->status === 'pending')
                                        <form action="{{ route('admin.guestbook.approve', $feedback->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success me-1" title="Setujui">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>

                                        {{-- Tombol Reject (jika pending) --}}
                                        <form action="{{ route('admin.guestbook.reject', $feedback->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning me-1" title="Tolak">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Tombol Delete --}}
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $feedback->id }}"
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                    {{-- Modal View Detail --}}
                                    <div class="modal fade" id="viewModal{{ $feedback->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Feedback #{{ $feedback->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <strong>Nama:</strong> {{ $feedback->name }}<br>
                                                            <strong>Email:</strong> {{ $feedback->email }}<br>
                                                            <strong>Status:</strong> 
                                                            @if($feedback->status === 'pending')
                                                                <span class="badge bg-warning">Pending</span>
                                                            @elseif($feedback->status === 'approved')
                                                                <span class="badge bg-success">Approved</span>
                                                            @else
                                                                <span class="badge bg-danger">Rejected</span>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Tanggal:</strong> {{ $feedback->created_at->format('d/m/Y H:i') }}<br>
                                                            <strong>Update:</strong> {{ $feedback->updated_at->format('d/m/Y H:i') }}
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div>
                                                        <strong>Pesan Feedback:</strong>
                                                        <div class="mt-2 p-3 bg-light border rounded">
                                                            {{ $feedback->message }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    @if($feedback->status === 'pending')
                                                        <form action="{{ route('admin.guestbook.approve', $feedback->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="bi bi-check-circle"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.guestbook.reject', $feedback->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-warning">
                                                                <i class="bi bi-x-circle"></i> Reject
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Modal Konfirmasi Hapus --}}
                                    <div class="modal fade" id="deleteModal{{ $feedback->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Konfirmasi Hapus Feedback</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Apakah Anda yakin ingin menghapus feedback dari <strong>{{ $feedback->name }}</strong>?</p>
                                                    <div class="alert alert-warning">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                        Aksi ini tidak dapat dibatalkan!
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <form action="{{ route('admin.guestbook.delete', $feedback->id) }}" method="POST" class="d-inline">
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
                    {{ $feedbacks->links() }}
                </div>
            @else
                {{-- Tampilan jika tidak ada feedback --}}
                <div class="text-center py-4">
                    <i class="bi bi-chat-left-text" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">Belum Ada Feedback</h4>
                    <p class="text-muted">Belum ada customer yang memberikan feedback.</p>
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