@extends('layouts.app')

{{-- Menambahkan style khusus untuk halaman dashboard --}}
@push('styles')
<style>
    {{-- Styling untuk menyamakan tinggi card menu admin --}}
    .admin-menu-card {
        height: 100%;
        min-height: 280px; /* Tinggi minimum untuk konsistensi tampilan */
        display: flex;
        flex-direction: column;
    }

    {{-- Styling untuk konten card agar terdistribusi secara merata --}}
    .admin-menu-card .card-body {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
        padding: 1.5rem;
    }

    {{-- Styling untuk teks card agar terpusat --}}
    .admin-menu-card .card-text {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        margin: 1rem 0;
    }
</style>
@endpush

{{-- Konten utama halaman dashboard admin --}}
@section('content')
<div class="container">
    {{-- Judul dan deskripsi dashboard --}}
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Dashboard Admin Tukupedia</h1>
            <p class="text-muted">Kelola sistem toko alat kesehatan online</p>
        </div>
    </div>
    
    {{-- Bagian statistik dalam card --}}
    <div class="row mb-4">
        {{-- Card untuk menampilkan total customer --}}
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Customer</h5>
                            <h2 class="card-text">{{ $jumlahCustomer }}</h2> {{-- Menampilkan jumlah customer --}}
                            <small>Pengguna customer</small>
                        </div>
                        <div>
                            <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.8;"></i> {{-- Ikon untuk customer --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Card untuk menampilkan jumlah pemilik toko --}}
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Pemilik Toko</h5>
                            <h2 class="card-text">{{ $jumlahPemilikToko }}</h2> {{-- Menampilkan jumlah pemilik toko --}}
                            <small>Toko aktif</small>
                        </div>
                        <div>
                            <i class="bi bi-shop" style="font-size: 3rem; opacity: 0.8;"></i> {{-- Ikon untuk toko --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Card untuk menampilkan permohonan toko yang tertunda --}}
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Permohonan Toko</h5>
                            <h2 class="card-text">{{ $jumlahTokoPending }}</h2> {{-- Menampilkan jumlah toko tertunda --}}
                            <small>Menunggu review</small>
                        </div>
                        <div>
                            <i class="bi bi-hourglass-split" style="font-size: 3rem; opacity: 0.8;"></i> {{-- Ikon untuk permohonan tertunda --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Card untuk menampilkan feedback yang tertunda --}}
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Feedback Pending</h5>
                            <h2 class="card-text">{{ $jumlahFeedbackPending }}</h2> {{-- Menampilkan jumlah feedback tertunda --}}
                            <small>Menunggu moderasi</small>
                        </div>
                        <div>
                            <i class="bi bi-chat-left-text-fill" style="font-size: 3rem; opacity: 0.8;"></i> {{-- Ikon untuk feedback --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Menu aksi cepat untuk admin --}}
    <div class="row">
        <div class="col-12">
            <h3 class="mb-3">Menu Utama Admin</h3> {{-- Judul menu utama --}}
        </div>
        
        {{-- Card untuk mengelola customer --}}
        <div class="col-md-4 mb-3">
            <div class="card admin-menu-card">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill" style="font-size: 3rem; color: #007bff;"></i> {{-- Ikon untuk kelola customer --}}
                    <h5 class="card-title mt-2">Kelola Customer</h5>
                    <p class="card-text">Manage database customer, edit dan hapus data customer</p>
                    <a href="{{ route('admin.customers') }}" class="btn btn-primary">Kelola Customer</a> {{-- Tombol navigasi ke halaman customer --}}
                </div>
            </div>
        </div>
        
        {{-- Card untuk mengelola kategori produk --}}
        <div class="col-md-4 mb-3">
            <div class="card admin-menu-card">
                <div class="card-body text-center">
                    <i class="bi bi-tags-fill" style="font-size: 3rem; color: #28a745;"></i> {{-- Ikon untuk kelola kategori --}}
                    <h5 class="card-title mt-2">Kelola Kategori</h5>
                    <p class="card-text">Add/Remove/Update kategori produk dengan harga</p>
                    <a href="{{ route('admin.kategori') }}" class="btn btn-success">Kelola Kategori</a> {{-- Tombol navigasi ke halaman kategori --}}
                </div>
            </div>
        </div>
        
        {{-- Card untuk mengelola permohonan toko --}}
        <div class="col-md-4 mb-3">
            <div class="card admin-menu-card">
                <div class="card-body text-center">
                    <i class="bi bi-shop" style="font-size: 3rem; color: #ffc107;"></i> {{-- Ikon untuk permohonan toko --}}
                    <h5 class="card-title mt-2">Permohonan Toko</h5>
                    <p class="card-text">Approve/Reject/Delete permohonan pembukaan toko</p>
                    <a href="{{ route('admin.toko.requests') }}" class="btn btn-warning">
                        Kelola Permohonan
                        @if($jumlahTokoPending > 0)
                            <span class="badge bg-danger">{{ $jumlahTokoPending }}</span> {{-- Menampilkan notifikasi jumlah permohonan tertunda --}}
                        @endif
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Card untuk mengelola guest book --}}
        <div class="col-md-4 mb-3">
            <div class="card admin-menu-card">
                <div class="card-body text-center">
                    <i class="bi bi-chat-left-text-fill" style="font-size: 3rem; color: #17a2b8;"></i> {{-- Ikon untuk guest book --}}
                    <h5 class="card-title mt-2">Guest Book</h5>
                    <p class="card-text">Moderasi feedback dari visitor dan customer</p>
                    <a href="{{ route('admin.guestbook') }}" class="btn btn-info">
                        Kelola Feedback
                        @if($jumlahFeedbackPending > 0)
                            <span class="badge bg-danger">{{ $jumlahFeedbackPending }}</span> {{-- Menampilkan notifikasi jumlah feedback tertunda --}}
                        @endif
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Card untuk mengelola pengiriman pesanan --}}
        <div class="col-md-4 mb-3">
            <div class="card admin-menu-card">
                <div class="card-body text-center">
                    <i class="bi bi-truck" style="font-size: 3rem; color: #6c757d;"></i> {{-- Ikon untuk pengiriman --}}
                    <h5 class="card-title mt-2">Shipping Order</h5>
                    <p class="card-text">Kelola pengiriman dan tracking pesanan customer</p>
                    <a href="{{ route('admin.shipping') }}" class="btn btn-secondary">
                        Kelola Pengiriman
                        @if($jumlahShippingPending > 0)
                            <span class="badge bg-danger">{{ $jumlahShippingPending }}</span> {{-- Menampilkan notifikasi jumlah pengiriman tertunda --}}
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection