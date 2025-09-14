@extends('layouts.app')

@section('content')
{{-- Dashboard Admin OSS --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Dashboard Admin OSS</h1>
        </div>
    </div>
    
    {{-- Card Statistics --}}
    <div class="row mb-4">
        {{-- Card Total Customer --}}
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Customer</h5>
                    <h2 class="card-text">{{ $jumlahCustomer }}</h2>
                    <small>Pengguna terdaftar</small>
                </div>
            </div>
        </div>
        
        {{-- Card Toko Pending --}}
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Toko Pending</h5>
                    <h2 class="card-text">{{ $jumlahTokoPending }}</h2>
                    <small>Menunggu persetujuan</small>
                </div>
            </div>
        </div>
        
        {{-- Card Feedback Pending --}}
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Feedback Pending</h5>
                    <h2 class="card-text">{{ $jumlahFeedbackPending }}</h2>
                    <small>Menunggu moderasi</small>
                </div>
            </div>
        </div>
        
        {{-- Card Shipping Pending --}}
        <div class="col-md-3 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Pengiriman Pending</h5>
                    <h2 class="card-text">{{ $jumlahShippingPending }}</h2>
                    <small>Belum dikirim</small>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Quick Action Menu --}}
    <div class="row">
        <div class="col-12">
            <h3 class="mb-3">Menu Utama Admin</h3>
        </div>
        
        {{-- Menu Card Manage Customers --}}
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill" style="font-size: 3rem; color: #007bff;"></i>
                    <h5 class="card-title mt-2">Kelola Customer</h5>
                    <p class="card-text">Manage database customer, edit dan hapus data customer</p>
                    <a href="{{ route('admin.customers') }}" class="btn btn-primary">Kelola Customer</a>
                </div>
            </div>
        </div>
        
        {{-- Menu Card Manage Kategori --}}
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-tags-fill" style="font-size: 3rem; color: #28a745;"></i>
                    <h5 class="card-title mt-2">Kelola Kategori</h5>
                    <p class="card-text">Add/Remove/Update kategori produk dengan harga</p>
                    <a href="{{ route('admin.kategori') }}" class="btn btn-success">Kelola Kategori</a>
                </div>
            </div>
        </div>
        
        {{-- Menu Card Manage Toko --}}
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-shop" style="font-size: 3rem; color: #ffc107;"></i>
                    <h5 class="card-title mt-2">Permohonan Toko</h5>
                    <p class="card-text">Approve/Reject permohonan pembukaan toko baru</p>
                    <a href="{{ route('admin.toko.requests') }}" class="btn btn-warning">Kelola Toko</a>
                </div>
            </div>
        </div>
        
        {{-- Menu Card Manage Guest Book --}}
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-chat-left-text-fill" style="font-size: 3rem; color: #17a2b8;"></i>
                    <h5 class="card-title mt-2">Guest Book</h5>
                    <p class="card-text">View/Delete guest book entries dan moderasi feedback</p>
                    <a href="{{ route('admin.guestbook') }}" class="btn btn-info">Kelola Feedback</a>
                </div>
            </div>
        </div>
        
        {{-- Menu Card Shipping Orders --}}
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-truck" style="font-size: 3rem; color: #6c757d;"></i>
                    <h5 class="card-title mt-2">Shipping Order</h5>
                    <p class="card-text">Kelola pengiriman dan tracking pesanan customer</p>
                    <a href="{{ route('admin.shipping') }}" class="btn btn-secondary">Kelola Pengiriman</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection