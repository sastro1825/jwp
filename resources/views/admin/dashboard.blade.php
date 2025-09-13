@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Dashboard Admin OSS</h1>
    <p>Jumlah Customer: {{ $jumlahCustomer }}</p>
    <p>Toko Pending: {{ $jumlahTokoPending }}</p>
    <a href="{{ route('admin.customers') }}" class="btn btn-primary">Manage Customers</a>
    <a href="{{ route('admin.kategori') }}" class="btn btn-primary">Manage Kategori</a>
    <a href="{{ route('admin.toko.requests') }}" class="btn btn-primary">Manage Permohonan Toko</a>
</div>
@endsection