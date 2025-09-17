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
        {{-- Daftar Transaksi dengan Detail Item --}}
        <div class="row">
            @foreach($transaksis as $transaksi)
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-0">
                                        <i class="bi bi-receipt"></i> 
                                        Pesanan #{{ $transaksi->id }}
                                        {{-- Status Shipping --}}
                                        @if($transaksi->shippingOrder)
                                            @if($transaksi->shippingOrder->status === 'pending')
                                                <span class="badge bg-warning ms-2">Pending</span>
                                            @elseif($transaksi->shippingOrder->status === 'shipped')
                                                <span class="badge bg-info ms-2">Dikirim</span>
                                            @elseif($transaksi->shippingOrder->status === 'delivered')
                                                <span class="badge bg-success ms-2">Delivered</span>
                                            @else
                                                <span class="badge bg-danger ms-2">Cancelled</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary ms-2">{{ ucfirst($transaksi->status) }}</span>
                                        @endif
                                    </h6>
                                    {{-- Format tanggal dengan timezone Indonesia --}}
                                    <small class="text-muted">{{ $transaksi->created_at->setTimezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <strong class="text-primary">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</strong>
                                    {{-- Tracking Number --}}
                                    @if($transaksi->shippingOrder && $transaksi->shippingOrder->tracking_number)
                                        <br><small class="text-muted">{{ $transaksi->shippingOrder->tracking_number }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    {{-- Detail Items yang Dibeli - DIPERBAIKI type casting --}}
                                    <h6>Barang yang Dipesan:</h6>
                                    @php
                                        // Ambil detail transaksi yang sebenarnya dari database
                                        $detailItems = \App\Models\DetailTransaksi::where('transaksi_id', $transaksi->id)->get();
                                    @endphp

                                    @if($detailItems->count() > 0)
                                        <div class="row">
                                            @foreach($detailItems as $item)
                                                <div class="col-md-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            {{-- Icon berdasarkan tipe item --}}
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                                 style="width: 60px; height: 60px;">
                                                                @if($item->item_type === 'toko_kategori')
                                                                    <i class="bi bi-shop text-success"></i>
                                                                @else
                                                                    <i class="bi bi-heart-pulse text-primary"></i>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">{{ $item->nama_item }}</h6>
                                                            <span class="badge bg-{{ $item->item_type === 'toko_kategori' ? 'success' : 'info' }}">
                                                                {{ $item->item_type === 'toko_kategori' ? 'Toko Mitra' : ucwords($item->item_type) }}
                                                            </span>
                                                            <br><small class="text-success">
                                                                {{ $item->jumlah }}x Rp {{ number_format((float)$item->harga_item, 0, ',', '.') }} = 
                                                                Rp {{ number_format((float)$item->subtotal_item, 0, ',', '.') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i>
                                            Detail barang tidak tersedia.
                                        </div>
                                    @endif

                                    {{-- Detail Pesanan --}}
                                    <hr>
                                    <h6>Detail Pesanan:</h6>
                                    <p class="text-muted mb-1">
                                        <strong>Metode Pembayaran:</strong> {{ ucfirst($transaksi->metode_pembayaran) }}
                                    </p>
                                    @if($transaksi->alamat_pengiriman)
                                        <p class="text-muted mb-1">
                                            <strong>Alamat Pengiriman:</strong><br>
                                            {{ $transaksi->alamat_pengiriman }}
                                        </p>
                                    @endif
                                    @if($transaksi->catatan)
                                        <p class="text-muted mb-0">
                                            <strong>Catatan:</strong> {{ $transaksi->catatan }}
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    {{-- Info Pengiriman --}}
                                    @if($transaksi->shippingOrder)
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6>Info Pengiriman</h6>
                                                <p class="mb-1">
                                                    <strong>Kurir:</strong> {{ $transaksi->shippingOrder->courier ?? 'Belum ditentukan' }}
                                                </p>
                                                <p class="mb-1">
                                                    <strong>No. Resi:</strong> {{ $transaksi->shippingOrder->tracking_number }}
                                                </p>
                                                @if($transaksi->shippingOrder->shipped_date)
                                                    <p class="mb-1">
                                                        <strong>Tanggal Kirim:</strong> {{ $transaksi->shippingOrder->shipped_date->format('d/m/Y') }}
                                                    </p>
                                                @endif
                                                @if($transaksi->shippingOrder->delivered_date)
                                                    <p class="mb-1">
                                                        <strong>Tanggal Sampai:</strong> {{ $transaksi->shippingOrder->delivered_date->format('d/m/Y') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- Action Buttons --}}
                                    <div class="text-end mt-3">
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