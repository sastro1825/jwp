@extends('layouts.app')

{{-- Bagian konten utama halaman --}}
@section('content')
{{-- Kontainer utama untuk form edit kategori --}}
<div class="container">
    {{-- Baris untuk judul dan informasi kategori --}}
    <div class="row">
        <div class="col-12">
            {{-- Judul halaman edit kategori --}}
            <h1>Edit Kategori</h1>
            {{-- Menampilkan nama kategori yang sedang diedit --}}
            <p class="text-muted">Perbarui data kategori: <strong>{{ $kategori->nama }}</strong></p>
        </div>
    </div>

    {{-- Menampilkan pesan sukses jika ada --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            {{-- Tombol untuk menutup alert --}}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Menampilkan pesan error jika ada validasi gagal --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    {{-- Menampilkan setiap pesan error --}}
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            {{-- Tombol untuk menutup alert error --}}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Baris untuk form edit kategori dengan penataan tengah --}}
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- Kartu untuk form edit kategori --}}
            <div class="card">
                {{-- Header kartu dengan judul form --}}
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square"></i> Form Edit Kategori
                    </h5>
                </div>
                {{-- Badan kartu berisi form --}}
                <div class="card-body">
                    {{-- Form untuk memperbarui kategori --}}
                    <form action="{{ route('admin.kategori.update', $kategori->id) }}" method="POST" enctype="multipart/form-data">
                        {{-- Token CSRF untuk keamanan form --}}
                        @csrf
                        {{-- Metode PATCH untuk update data --}}
                        @method('PATCH')

                        {{-- Baris untuk input nama dan jenis kategori --}}
                        <div class="row">
                            {{-- Input untuk nama kategori --}}
                            <div class="col-md-6 mb-3">
                                <label for="nama" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" 
                                       id="nama" 
                                       name="nama" 
                                       class="form-control @error('nama') is-invalid @enderror" 
                                       value="{{ old('nama', $kategori->nama) }}" 
                                       required>
                                {{-- Menampilkan pesan error untuk nama jika ada --}}
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Dropdown untuk memilih jenis kategori --}}
                            <div class="col-md-6 mb-3">
                                <label for="category_type" class="form-label">Jenis Kategori <span class="text-danger">*</span></label>
                                <select id="category_type" 
                                        name="category_type" 
                                        class="form-control @error('category_type') is-invalid @enderror" 
                                        required>
                                    <option value="">Pilih Jenis Kategori...</option>
                                    <option value="obat-obatan" {{ old('category_type', $kategori->category_type) == 'obat-obatan' ? 'selected' : '' }}>Obat-obatan</option>
                                    <option value="alat-kesehatan" {{ old('category_type', $kategori->category_type) == 'alat-kesehatan' ? 'selected' : '' }}>Alat Kesehatan</option>
                                    <option value="suplemen-kesehatan" {{ old('category_type', $kategori->category_type) == 'suplemen-kesehatan' ? 'selected' : '' }}>Suplemen Kesehatan</option>
                                    <option value="kesehatan-pribadi" {{ old('category_type', $kategori->category_type) == 'kesehatan-pribadi' ? 'selected' : '' }}>Kesehatan Pribadi</option>
                                    <option value="perawatan-kecantikan" {{ old('category_type', $kategori->category_type) == 'perawatan-kecantikan' ? 'selected' : '' }}>Perawatan & Kecantikan</option>
                                    <option value="gizi-nutrisi" {{ old('category_type', $kategori->category_type) == 'gizi-nutrisi' ? 'selected' : '' }}>Gizi & Nutrisi Medis</option>
                                    <option value="kesehatan-lingkungan" {{ old('category_type', $kategori->category_type) == 'kesehatan-lingkungan' ? 'selected' : '' }}>Kesehatan Lingkungan</option>
                                </select>
                                {{-- Menampilkan pesan error untuk jenis kategori jika ada --}}
                                @error('category_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Baris untuk input harga dan tampilan jenis saat ini --}}
                        <div class="row">
                            {{-- Input untuk harga patokan kategori --}}
                            <div class="col-md-6 mb-3">
                                <label for="harga" class="form-label">Harga Patokan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" 
                                           id="harga" 
                                           name="harga" 
                                           class="form-control @error('harga') is-invalid @enderror" 
                                           value="{{ old('harga', $kategori->harga) }}" 
                                           required 
                                           min="0" 
                                           step="1000">
                                </div>
                                {{-- Menampilkan pesan error untuk harga jika ada --}}
                                @error('harga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Menampilkan jenis kategori saat ini --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Saat Ini</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-info fs-6">
                                        {{-- Mengambil label jenis kategori menggunakan method getCategoryTypeLabel --}}
                                        {{ $kategori->getCategoryTypeLabel() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Baris untuk input gambar dan pratinjau gambar saat ini --}}
                        <div class="row">
                            {{-- Input untuk unggah gambar kategori --}}
                            <div class="col-md-6 mb-3">
                                <label for="gambar" class="form-label">Gambar Kategori</label>
                                <input type="file" 
                                       id="gambar" 
                                       name="gambar" 
                                       class="form-control @error('gambar') is-invalid @enderror" 
                                       accept="image/jpeg,image/png,image/jpg,image/gif">
                                {{-- Menampilkan pesan error untuk gambar jika ada --}}
                                @error('gambar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                {{-- Informasi format dan ukuran maksimum file gambar --}}
                                <small class="form-text text-muted">Format: JPEG, PNG, JPG, GIF (Max: 2MB)</small>
                            </div>

                            {{-- Menampilkan pratinjau gambar kategori saat ini --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gambar Saat Ini</label>
                                <div>
                                    @if($kategori->gambar)
                                        {{-- Menampilkan gambar jika tersedia di storage --}}
                                        <img src="{{ Storage::url($kategori->gambar) }}" 
                                             alt="{{ $kategori->nama }}" 
                                             class="img-thumbnail" 
                                             style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                        {{-- Menampilkan nama file gambar --}}
                                        <p class="text-muted mt-2">{{ basename($kategori->gambar) }}</p>
                                    @else
                                        {{-- Placeholder jika tidak ada gambar --}}
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 150px; height: 150px; border: 1px solid #ddd;">
                                            <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                        <p class="text-muted mt-2">Belum ada gambar</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Input untuk deskripsi kategori --}}
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea id="deskripsi" 
                                          name="deskripsi" 
                                          class="form-control @error('deskripsi') is-invalid @enderror" 
                                          rows="4">{{ old('deskripsi', $kategori->deskripsi) }}</textarea>
                                {{-- Menampilkan pesan error untuk deskripsi jika ada --}}
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Informasi tambahan tentang kategori --}}
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle"></i> Informasi Kategori</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            {{-- Menampilkan ID kategori --}}
                                            <strong>ID Kategori:</strong> {{ $kategori->id }}
                                        </div>
                                        <div class="col-md-4">
                                            {{-- Menampilkan jumlah produk dalam kategori --}}
                                            <strong>Jumlah Produk:</strong> {{ $kategori->produks->count() }}
                                        </div>
                                        <div class="col-md-4">
                                            {{-- Menampilkan tanggal pembuatan kategori --}}
                                            <strong>Dibuat:</strong> {{ $kategori->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            {{-- Menampilkan jenis kategori lama --}}
                                            <strong>Jenis Lama:</strong> {{ $kategori->getCategoryTypeLabel() }}
                                        </div>
                                        <div class="col-md-4">
                                            {{-- Menampilkan harga lama dengan format mata uang --}}
                                            <strong>Harga Lama:</strong> Rp {{ number_format($kategori->harga, 0, ',', '.') }}
                                        </div>
                                        <div class="col-md-4">
                                            {{-- Menampilkan tanggal pembaruan terakhir --}}
                                            <strong>Update Terakhir:</strong> {{ $kategori->updated_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol untuk submit form dan batal --}}
                        <div class="text-end">
                            {{-- Tombol batal untuk kembali ke daftar kategori --}}
                            <a href="{{ route('admin.kategori') }}" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            {{-- Tombol untuk memperbarui kategori --}}
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Perbarui Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection