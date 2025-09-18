@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Judul halaman --}}
    <h1>Permohonan Toko Pending</h1>
    {{-- Looping untuk menampilkan daftar toko --}}
    @foreach($tokos as $toko)
    <div class="card">
        {{-- Menampilkan nama toko dan nama pemilik --}}
        <h5>{{ $toko->nama }} - Pemilik: {{ $toko->user->name }}</h5>
        {{-- Form untuk menyetujui toko --}}
        <form action="{{ route('admin.toko.approve', $toko->id) }}" method="POST" style="display:inline;">
            {{-- Token CSRF untuk keamanan form --}}
            @csrf
            {{-- Tombol untuk menyetujui permohonan toko --}}
            <button type="submit" class="btn btn-success">Approve</button>
        </form>
        {{-- Form untuk menolak toko --}}
        <form action="{{ route('admin.toko.reject', $toko->id) }}" method="POST" style="display:inline;">
            {{-- Token CSRF untuk keamanan form --}}
            @csrf
            {{-- Tombol untuk menolak permohonan toko --}}
            <button type="submit" class="btn btn-danger">Reject</button>
        </form>
    </div>
    {{-- Akhir dari loop toko --}}
    @endforeach
</div>
{{-- Akhir dari section konten --}}
@endsection