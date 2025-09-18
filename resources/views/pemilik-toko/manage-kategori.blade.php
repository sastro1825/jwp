@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            {{-- Judul halaman --}}
            <h1 class="mb-4">Kelola Kategori Toko</h1>
            @if(isset($toko) && $toko)
                {{-- Menampilkan nama toko jika tersedia --}}
                <p class="text-muted">Kelola kategori untuk toko: <strong>{{ $toko->nama }}</strong></p>
            @else
                {{-- Peringatan jika toko belum aktif --}}
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Anda belum memiliki toko yang aktif. Silakan hubungi admin untuk aktivasi toko.
                </div>
            @endif
        </div>
    </div>

    {{-- Menampilkan pesan sukses jika ada --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Menampilkan pesan error jika ada --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(isset($toko) && $toko)
        {{-- Tombol untuk membuka modal tambah kategori --}}
        <div class="mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKategoriModal">
                <i class="bi bi-plus-circle"></i> Tambah Kategori Baru
            </button>
        </div>

        {{-- Kartu untuk menampilkan daftar kategori --}}
        <div class="card">
            <div class="card-header">
                {{-- Judul kartu dengan jumlah kategori --}}
                <h5 class="mb-0">Kategori Toko Anda ({{ $kategoris->total() }} kategori)</h5>
            </div>
            <div class="card-body">
                @if($kategoris->count() > 0)
                    {{-- Tabel responsif untuk daftar kategori --}}
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
                                            {{-- Menampilkan gambar kategori jika ada --}}
                                            <img src="{{ asset('storage/' . $kategori->gambar) }}" 
                                                 alt="{{ $kategori->nama }}" 
                                                 class="rounded"
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            {{-- Placeholder jika gambar tidak ada --}}
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    {{-- Nama kategori --}}
                                    <td><strong>{{ $kategori->nama }}</strong></td>
                                    <td>
                                        {{-- Menampilkan tipe kategori dengan badge --}}
                                        <span class="badge bg-info">
                                            {{ $kategori->getCategoryTypeLabel() }}
                                        </span>
                                    </td>
                                    {{-- Harga kategori dengan format --}}
                                    <td><strong class="text-success">{{ $kategori->formatted_harga }}</strong></td>
                                    {{-- Deskripsi singkat kategori --}}
                                    <td>{{ $kategori->short_description }}</td>
                                    {{-- Tanggal pembuatan kategori --}}
                                    <td>{{ $kategori->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        {{-- Tombol edit kategori --}}
                                        <a href="{{ route('pemilik-toko.kategori.edit', $kategori->id) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        {{-- Form untuk menghapus kategori --}}
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
                    {{-- Navigasi paginasi --}}
                    {{ $kategoris->links() }}
                @else
                    {{-- Pesan jika belum ada kategori --}}
                    <div class="text-center py-4">
                        <i class="bi bi-tags" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3">Belum Ada Kategori</h4>
                        <p class="text-muted">Mulai tambahkan kategori untuk toko Anda.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal untuk menambah kategori baru --}}
        <div class="modal fade" id="addKategoriModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('pemilik-toko.kategori.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            {{-- Judul modal --}}
                            <h5 class="modal-title">Tambah Kategori Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Menampilkan pesan error validasi jika ada --}}
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            {{-- Input nama kategori --}}
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Kategori *</label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                                       id="nama" name="nama" value="{{ old('nama') }}" required>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            {{-- Pilihan tipe kategori --}}
                            <div class="mb-3">
                                <label for="category_type" class="form-label">Tipe Kategori *</label>
                                <select class="form-select @error('category_type') is-invalid @enderror" 
                                        id="category_type" name="category_type" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="alat-kesehatan" {{ old('category_type') == 'alat-kesehatan' ? 'selected' : '' }}>Alat Kesehatan</option>
                                    <option value="obat-obatan" {{ old('category_type') == 'obat-obatan' ? 'selected' : '' }}>Obat-obatan</option>
                                    <option value="suplemen-kesehatan" {{ old('category_type') == 'suplemen-kesehatan' ? 'selected' : '' }}>Suplemen Kesehatan</option>
                                    <option value="perawatan-kecantikan" {{ old('category_type') == 'perawatan-kecantikan' ? 'selected' : '' }}>Perawatan & Kecantikan</option>
                                    <option value="kesehatan-pribadi" {{ old('category_type') == 'kesehatan-pribadi' ? 'selected' : '' }}>Kesehatan Pribadi</option>
                                </select>
                                @error('category_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            {{-- Input harga kategori --}}
                            <div class="mb-3">
                                <label for="harga" class="form-label">Harga *</label>
                                <input type="number" class="form-control @error('harga') is-invalid @enderror" 
                                       id="harga" name="harga" value="{{ old('harga') }}" min="0" step="0.01" required>
                                @error('harga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            {{-- Input deskripsi kategori --}}
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                          id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            {{-- Input untuk unggah gambar --}}
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Gambar</label>
                                <input type="file" class="form-control @error('gambar') is-invalid @enderror" 
                                       id="gambar" name="gambar" accept="image/*">
                                <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB.</small>
                                @error('gambar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            {{-- Tombol batal dan simpan pada modal --}}
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Tombol kembali ke dashboard --}}
    <div class="mt-4">
        <a href="{{ route('pemilik-toko.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection