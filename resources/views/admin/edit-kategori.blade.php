@extends('layouts.app')

@section('content')
{{-- Form Edit Kategori dengan Category Type Dropdown --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Edit Kategori</h1>
            <p class="text-muted">Perbarui data kategori: <strong>{{ $kategori->nama }}</strong></p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
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

    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- Form Edit Kategori --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square"></i> Form Edit Kategori
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.kategori.update', $kategori->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            {{-- Nama Kategori --}}
                            <div class="col-md-6 mb-3">
                                <label for="nama" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" 
                                       id="nama" 
                                       name="nama" 
                                       class="form-control @error('nama') is-invalid @enderror" 
                                       value="{{ old('nama', $kategori->nama) }}" 
                                       required>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Jenis Kategori Kesehatan --}}
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
                                @error('category_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Harga Kategori --}}
                        <div class="row">
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
                                @error('harga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Current Category Type Display --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Saat Ini</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-info fs-6">
                                        {{ $kategori->getCategoryTypeLabel() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Gambar Kategori --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
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

                            {{-- Preview Gambar Saat Ini --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gambar Saat Ini</label>
                                <div>
                                    @if($kategori->gambar)
                                        <img src="{{ Storage::url($kategori->gambar) }}" 
                                             alt="{{ $kategori->nama }}" 
                                             class="img-thumbnail" 
                                             style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                        <p class="text-muted mt-2">{{ basename($kategori->gambar) }}</p>
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 150px; height: 150px; border: 1px solid #ddd;">
                                            <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                        <p class="text-muted mt-2">Belum ada gambar</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Deskripsi Kategori --}}
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea id="deskripsi" 
                                          name="deskripsi" 
                                          class="form-control @error('deskripsi') is-invalid @enderror" 
                                          rows="4">{{ old('deskripsi', $kategori->deskripsi) }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Info Kategori --}}
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle"></i> Informasi Kategori</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>ID Kategori:</strong> {{ $kategori->id }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Jumlah Produk:</strong> {{ $kategori->produks->count() }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Dibuat:</strong> {{ $kategori->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <strong>Jenis Lama:</strong> {{ $kategori->getCategoryTypeLabel() }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Harga Lama:</strong> Rp {{ number_format($kategori->harga, 0, ',', '.') }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Update Terakhir:</strong> {{ $kategori->updated_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Submit --}}
                        <div class="text-end">
                            <a href="{{ route('admin.kategori') }}" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
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