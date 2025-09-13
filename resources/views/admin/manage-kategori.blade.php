@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Kelola Kategori</h1>
    <!-- Form Tambah -->
    <form action="{{ route('admin.kategori.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label>Nama Kategori</label>
            <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Gambar</label>
            <input type="file" name="gambar" class="form-control">
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Tambah Kategori</button>
    </form>

    <!-- List Kategori -->
    <h2>Daftar Kategori</h2>
    <table class="table">
        <thead><tr><th>ID</th><th>Nama</th><th>Gambar</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach($kategoris as $kategori)
            <tr>
                <td>{{ $kategori->id }}</td>
                <td>{{ $kategori->nama }}</td>
                <td>
                    @if($kategori->gambar)
                    <img src="{{ Storage::url($kategori->gambar) }}" width="50">
                    @endif
                </td>
                <td>
                    <a href="#" class="btn btn-warning">Edit</a> <!-- Tambah form edit jika perlu -->
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection