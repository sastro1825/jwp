@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Tombol kembali ke dashboard dipindah ke atas --}}
    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Kelola Guest Book & Feedback</h1>
            <p class="text-muted">Moderasi feedback dari visitor dan customer</p>
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
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Total Pending</h5>
                            <h2>{{ $totalPending }}</h2>
                            <small>Menunggu moderasi</small>
                        </div>
                        <div>
                            <i class="bi bi-hourglass-split" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Total Approved</h5>
                            <h2>{{ $totalApproved }}</h2>
                            <small>Sudah disetujui</small>
                        </div>
                        <div>
                            <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Total Rejected</h5>
                            <h2>{{ $totalRejected }}</h2>
                            <small>Ditolak</small>
                        </div>
                        <div>
                            <i class="bi bi-x-circle" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Total Feedback</h5>
                            <h2>{{ $totalFeedback }}</h2>
                            <small>Semua feedback</small>
                        </div>
                        <div>
                            <i class="bi bi-chat-left-text-fill" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Semua Feedback --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-chat-square-text"></i> Semua Feedback (Visitor & Customer)
            </h5>
        </div>
        <div class="card-body">
            @if($allFeedbacks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tanggal</th>
                                <th>Pengirim</th>
                                <th>Tipe</th>
                                <th>Pesan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allFeedbacks as $feedback)
                            <tr>
                                <td>{{ $feedback->id }}</td>
                                <td>{{ $feedback->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <strong>{{ $feedback->getSenderName() }}</strong><br>
                                    <small class="text-muted">{{ $feedback->getSenderEmail() }}</small>
                                </td>
                                <td>
                                    @if($feedback->isFromCustomer())
                                        <span class="badge bg-primary">Customer</span>
                                    @else
                                        <span class="badge bg-secondary">Visitor</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="max-width: 300px;">
                                        {{ Str::limit($feedback->message, 100) }}
                                        @if(strlen($feedback->message) > 100)
                                            <button class="btn btn-sm btn-link p-0" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#messageModal{{ $feedback->id }}">
                                                Lihat selengkapnya
                                            </button>
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
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($feedback->status === 'pending')
                                            {{-- Approve Button --}}
                                            <form action="{{ route('admin.guestbook.approve', $feedback->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>
                                            {{-- Reject Button --}}
                                            <form action="{{ route('admin.guestbook.reject', $feedback->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" title="Reject">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </form>
                                        @endif
                                        {{-- Delete Button --}}
                                        <form action="{{ route('admin.guestbook.delete', $feedback->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete" 
                                                    onclick="return confirm('Yakin hapus feedback ini?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal untuk Pesan Lengkap --}}
                            @if(strlen($feedback->message) > 100)
                                <div class="modal fade" id="messageModal{{ $feedback->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Pesan Lengkap</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <strong>Dari:</strong> {{ $feedback->getSenderName() }}<br>
                                                    <strong>Email:</strong> {{ $feedback->getSenderEmail() }}<br>
                                                    <strong>Tipe:</strong> 
                                                    @if($feedback->isFromCustomer())
                                                        <span class="badge bg-primary">Customer</span>
                                                    @else
                                                        <span class="badge bg-secondary">Visitor</span>
                                                    @endif
                                                </div>
                                                <div class="alert alert-light">
                                                    {{ $feedback->message }}
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                @if($feedback->status === 'pending')
                                                    <form action="{{ route('admin.guestbook.approve', $feedback->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bi bi-check"></i> Approve
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.guestbook.reject', $feedback->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-warning">
                                                            <i class="bi bi-x"></i> Reject
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $allFeedbacks->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-chat-left-text" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">Belum Ada Feedback</h4>
                    <p class="text-muted">Feedback dari visitor dan customer akan tampil di sini.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection