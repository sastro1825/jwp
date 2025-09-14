@extends('layouts.app')

@section('content')
{{-- Halaman Kelola Kategori --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Kelola Kategori</h1>
            <p class="text-muted">Add/Remove/Update Item Category - dengan input harga kategori</p>
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Form Tambah Kategori --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle"></i> Tambah Kategori Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.kategori.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Nama Kategori --}}
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" 
                                   id="nama" 
                                   name="nama" 
                                   class="form-control @error('nama') is-invalid @enderror" 
                                   value="{{ old('nama') }}" 
                                   required 
                                   placeholder="Contoh: Tensimeter">
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Harga Kategori --}}
                        <div class="mb-3">
                            <label for="harga" class="form-label">Harga Kategori <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp.</span>
                                <input type="number" 
                                       id="harga" 
                                       name="harga" 
                                       class="form-control @error('harga') is-invalid @enderror" 
                                       value="{{ old('harga') }}" 
                                       required 
                                       min="0" 
                                       step="1000"
                                       placeholder="0">
                            </div>
                            @error('harga')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Harga patokan untuk kategori ini</small>
                        </div>

                        {{-- Gambar Kategori --}}
                        <div class="mb-3">
                            <label for="gambar" class="form-label">Gambar Kategori</label>
                            <input type="file" 
                                   id="gambar" 
                                   name="gambar" 
                                   class="form-control @error('gambar') is-invalid @enderror" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif">
                            @error('gambar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Format: JPEG, PNG, JPG, GIF (Max: 2MB)</small>
                        </div>

                        {{-- Deskripsi Kategori --}}
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea id="deskripsi" 
                                      name="deskripsi" 
                                      class="form-control @error('deskripsi') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="Deskripsi kategori...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tombol Submit --}}
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Tambah Kategori
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- List Kategori --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Daftar Kategori
                    </h5>
                </div>
                <div class="card-body">
                    @if($kategoris->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Gambar</th>
                                        <th>Nama</th>
                                        <th>Harga</th>
                                        <th>Deskripsi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kategoris as $kategori)
                                    <tr>
                                        <td>{{ $kategori->id }}</td>
                                        <td>
                                            @if($kategori->gambar)
                                                <img src="{{ Storage::url($kategori->gambar) }}" 
                                                     alt="{{ $kategori->nama }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px; border: 1px solid #ddd;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $kategori->nama }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                Rp {{ number_format($kategori->harga ?? 0, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ Str::limit($kategori->deskripsi ?? '-', 50) }}
                                        </td>
                                        <td>
                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('admin.kategori.edit', $kategori->id) }}" 
                                               class="btn btn-sm btn-warning me-1" 
                                               title="Edit Kategori">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            
                                            {{-- Tombol Hapus --}}
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal{{ $kategori->id }}"
                                                    title="Hapus Kategori">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            
                                            {{-- Modal Konfirmasi Hapus --}}
                                            <div class="modal fade" id="deleteModal{{ $kategori->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Konfirmasi Hapus Kategori</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Apakah Anda yakin ingin menghapus kategori <strong>{{ $kategori->nama }}</strong>?</p>
                                                            <p class="text-danger">
                                                                <i class="bi bi-exclamation-triangle"></i>
                                                                Semua produk dalam kategori ini akan terpengaruh!
                                                            </p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <form action="{{ route('admin.kategori.delete', $kategori->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Links --}}
                        <div class="mt-3">
                            {{ $kategoris->links() }}
                        </div>
                    @else
                        {{-- Tampilan jika tidak ada kategori --}}
                        <div class="text-center py-4">
                            <i class="bi bi-tags" style="font-size: 4rem; color: #ccc;"></i>
                            <h4 class="mt-3">Belum Ada Kategori</h4>
                            <p class="text-muted">Mulai dengan menambahkan kategori produk pertama.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tombol Kembali ke Dashboard --}}
    <div class="mt-3">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection