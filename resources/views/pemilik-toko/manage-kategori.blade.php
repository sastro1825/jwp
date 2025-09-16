@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Kelola Kategori Toko: {{ $toko->nama }}</h1>
            <p class="text-muted">Kelola kategori produk khusus untuk toko Anda (Database Terpisah)</p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tombol Tambah Kategori --}}
    <div class="mb-4">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKategoriModal">
            <i class="bi bi-plus-circle"></i> Tambah Kategori Baru
        </button>
    </div>

    {{-- Tabel Kategori Toko --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Kategori Toko Anda ({{ $kategoris->total() }} kategori)</h5>
        </div>
        <div class="card-body">
            @if($kategoris->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama Kategori</th>
                                <th>Tipe</th>
                                <th>Harga</th>
                                <th>Deskripsi</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kategoris as $kategori)
                            <tr>
                                <td>
                                    @if($kategori->gambar)
                                        <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                                             alt="{{ $kategori->nama }}" 
                                             class="rounded"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td><strong>{{ $kategori->nama }}</strong></td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $kategori->getCategoryTypeLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-success">{{ $kategori->formatted_harga }}</strong>
                                </td>
                                <td>{{ $kategori->short_description }}</td>
                                <td>{{ $kategori->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('pemilik-toko.kategori.edit', $kategori->id) }}" 
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('pemilik-toko.kategori.delete', $kategori->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Yakin hapus kategori ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $kategoris->links() }}
            @else
                <div class="text-center py-4">
                    <i class="bi bi-tags" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">Belum Ada Kategori</h4>
                    <p class="text-muted">Mulai tambahkan kategori untuk toko Anda.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('pemilik-toko.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

{{-- Modal Tambah Kategori --}}
<div class="modal fade" id="addKategoriModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pemilik-toko.kategori.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_type" class="form-label">Tipe Kategori</label>
                        <select class="form-select" id="category_type" name="category_type" required>
                            <option value="">Pilih Tipe</option>
                            <option value="alat-kesehatan">Alat Kesehatan</option>
                            <option value="obat-obatan">Obat-obatan</option>
                            <option value="suplemen-kesehatan">Suplemen Kesehatan</option>
                            <option value="perawatan-kecantikan">Perawatan & Kecantikan</option>
                            <option value="kesehatan-pribadi">Kesehatan Pribadi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="harga" class="form-label">Harga</label>
                        <input type="number" class="form-control" id="harga" name="harga" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="gambar" class="form-label">Gambar</label>
                        <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection