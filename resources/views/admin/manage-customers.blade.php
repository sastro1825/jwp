@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard Admin OSS</h1>
        <div class="text-sm text-gray-500">
            Selamat datang, {{ Auth::user()->name }}!
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700">Jumlah Customer</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $jumlahCustomer }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700">Toko Pending</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $jumlahTokoPending }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700">Total Produk</h3>
            <p class="text-3xl font-bold text-green-600">{{ \App\Models\Produk::count() }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('admin.customers') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-center transition duration-300">
            Manage Customers
        </a>
        <a href="{{ route('admin.kategori') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-center transition duration-300">
            Manage Kategori
        </a>
        <a href="{{ route('admin.toko.requests') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded-lg text-center transition duration-300">
            Manage Permohonan Toko
        </a>
    </div>
</div>
@endsection