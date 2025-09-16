@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Riwayat Pesanan</h1>
            <p class="text-muted">
                Lihat semua pesanan yang pernah Anda buat
                @if(auth()->user()->role === 'pemilik_toko')
                    <span class="badge bg-success ms-2">Pemilik Toko</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Navigation Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('customer.area') }}">Customer Area</a>
            </li>
            <li class="breadcrumb-item active">Riwayat Pesanan</li>
        </ol>
    </nav>

    @if($transaksis->count() > 0)
        {{-- Daftar Transaksi --}}
        <div class="row">
            @foreach($transaksis as $transaksi)
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-0">
                                        <i class="bi bi-receipt"></i> 
                                        Pesanan #{{ $transaksi->id }}
                                        <span class="badge bg-{{ $transaksi->status === 'pending' ? 'warning' : ($transaksi->status === 'completed' ? 'success' : 'danger') }} ms-2">
                                            {{ ucfirst($transaksi->status) }}
                                        </span>
                                    </h6>
                                    <small class="text-muted">{{ $transaksi->created_at->format('d F Y, H:i') }}</small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <strong class="text-primary">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Detail Pesanan:</h6>
                                    <p class="text-muted mb-1">
                                        <strong>Metode Pembayaran:</strong> {{ ucfirst($transaksi->metode_pembayaran) }}
                                    </p>
                                    <p class="text-muted mb-1">
                                        <strong>Alamat Pengiriman:</strong><br>
                                        {{ $transaksi->alamat_pengiriman }}
                                    </p>
                                    @if($transaksi->catatan)
                                        <p class="text-muted mb-0">
                                            <strong>Catatan:</strong> {{ $transaksi->catatan }}
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="text-end">
                                        @if($transaksi->status === 'pending')
                                            <form action="{{ 
                                                auth()->user()->role === 'pemilik_toko' 
                                                    ? route('pemilik-toko.cancel.order', $transaksi->id) 
                                                    : route('customer.cancel.order', $transaksi->id) 
                                            }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Yakin batalkan pesanan ini?')">
                                                    <i class="bi bi-x-circle"></i> Batalkan
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <a href="{{ 
                                            auth()->user()->role === 'pemilik_toko' 
                                                ? route('pemilik-toko.download.laporan', $transaksi->id) 
                                                : route('customer.download.laporan', $transaksi->id) 
                                        }}" class="btn btn-outline-primary btn-sm ms-1">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $transaksis->links() }}
        </div>
    @else
        {{-- Tidak ada pesanan --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-receipt" style="font-size: 5rem; color: #ccc;"></i>
                <h4 class="mt-3">Belum Ada Pesanan</h4>
                <p class="text-muted">Anda belum pernah melakukan pemesanan. Ayo mulai berbelanja!</p>
                <a href="{{ route('customer.area') }}" class="btn btn-primary">
                    <i class="bi bi-shop"></i> Mulai Berbelanja
                </a>
            </div>
        </div>
    @endif

    {{-- Navigation Buttons --}}
    <div class="mt-4">
        <a href="{{ route('customer.area') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Kembali ke Customer Area
        </a>
        
        @if(auth()->user()->role === 'pemilik_toko')
            <a href="{{ route('pemilik-toko.keranjang') }}" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-cart3"></i> Lihat Keranjang
            </a>
        @else
            <a href="{{ route('customer.keranjang') }}" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-cart3"></i> Lihat Keranjang
            </a>
        @endif
    </div>
</div>
@endsection