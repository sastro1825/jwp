@extends('layouts.app')

@section('content')
{{-- Bagian konten utama untuk form permohonan toko --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Permohonan Pembukaan Toko</h1>
            <p class="text-muted">Ajukan permohonan untuk membuka toko online Anda di Tukupedia</p>
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

    {{-- Menampilkan daftar kesalahan validasi jika ada --}}
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

    {{-- Mengecek status permohonan toko pengguna --}}
    @if($existingRequest)
        {{-- Notifikasi jika ada permohonan yang sedang diproses --}}
        <div class="alert alert-warning">
            <h5><i class="bi bi-hourglass-split"></i> Permohonan Sedang Diproses</h5>
            <p class="mb-0">Anda sudah memiliki permohonan toko yang sedang dalam review admin. Silakan tunggu konfirmasi dari admin.</p>
            <a href="{{ route('customer.toko.status') }}" class="btn btn-primary btn-sm mt-2">Lihat Status Permohonan</a>
        </div>
    @elseif($approvedRequest)
        {{-- Notifikasi jika permohonan toko sudah disetujui --}}
        <div class="alert alert-success">
            <h5><i class="bi bi-check-circle"></i> Toko Sudah Disetujui</h5>
            <p class="mb-0">Selamat! Toko Anda sudah disetujui. Anda sekarang memiliki akses admin toko.</p>
            <a href="{{ route('customer.toko.status') }}" class="btn btn-success btn-sm mt-2">Kelola Toko</a>
        </div>
    @else
        {{-- Mengecek permohonan yang ditolak untuk ditampilkan --}}
        @php
            // Mengambil data permohonan yang ditolak terbaru berdasarkan user_id
            $rejectedRequest = \App\Models\TokoRequest::where('user_id', auth()->id())
                ->where('status', 'rejected')
                ->latest()
                ->first();
        @endphp
        
        @if($rejectedRequest)
            {{-- Notifikasi jika permohonan sebelumnya ditolak --}}
            <div class="alert alert-danger">
                <h5><i class="bi bi-x-circle"></i> Permohonan Sebelumnya Ditolak</h5>
                <p class="mb-2">Permohonan toko "<strong>{{ $rejectedRequest->nama_toko }}</strong>" telah ditolak oleh admin.</p>
                @if($rejectedRequest->catatan_admin)
                    <p class="mb-2"><strong>Alasan:</strong> {{ $rejectedRequest->catatan_admin }}</p>
                @endif
                <p class="mb-0">Anda dapat mengajukan permohonan baru dengan memperbaiki hal-hal yang menjadi alasan penolakan.</p>
            </div>
        @endif

        {{-- Form untuk mengajukan permohonan toko baru --}}
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-shop"></i> 
                            @if($rejectedRequest)
                                Form Permohonan Toko Baru (Setelah Penolakan)
                            @else
                                Form Permohonan Toko Baru
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('customer.toko.submit') }}" method="POST">
                            @csrf {{-- Token CSRF untuk keamanan form --}}

                            {{-- Menampilkan informasi pengguna yang login --}}
                            <div class="alert alert-info">
                                <h6><i class="bi bi-person-circle"></i> Informasi Pemohon</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Nama:</strong> {{ $user->name }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Email:</strong> {{ $user->email }}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- Input untuk nama toko --}}
                                <div class="col-md-6 mb-3">
                                    <label for="nama_toko" class="form-label">Nama Toko <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           id="nama_toko" 
                                           name="nama_toko" 
                                           class="form-control @error('nama_toko') is-invalid @enderror" 
                                           value="{{ old('nama_toko') }}" 
                                           required 
                                           placeholder="Contoh: Apotek Sehat Sejahtera">
                                    @error('nama_toko')
                                        <div class="invalid-feedback">{{ $message }}</div> {{-- Pesan error validasi nama toko --}}
                                    @enderror
                                </div>

                                {{-- Input untuk kategori usaha --}}
                                <div class="col-md-6 mb-3">
                                    <label for="kategori_usaha" class="form-label">Kategori Usaha <span class="text-danger">*</span></label>
                                    <select id="kategori_usaha" 
                                            name="kategori_usaha" 
                                            class="form-control @error('kategori_usaha') is-invalid @enderror" 
                                            required>
                                        <option value="">Pilih Kategori Usaha...</option>
                                        <option value="obat-obatan" {{ old('kategori_usaha') == 'obat-obatan' ? 'selected' : '' }}>Obat-obatan</option>
                                        <option value="alat-kesehatan" {{ old('kategori_usaha') == 'alat-kesehatan' ? 'selected' : '' }}>Alat Kesehatan</option>
                                        <option value="suplemen-kesehatan" {{ old('kategori_usaha') == 'suplemen-kesehatan' ? 'selected' : '' }}>Suplemen Kesehatan</option>
                                        <option value="kesehatan-pribadi" {{ old('kategori_usaha') == 'kesehatan-pribadi' ? 'selected' : '' }}>Kesehatan Pribadi</option>
                                        <option value="perawatan-kecantikan" {{ old('kategori_usaha') == 'perawatan-kecantikan' ? 'selected' : '' }}>Perawatan & Kecantikan</option>
                                        <option value="gizi-nutrisi" {{ old('kategori_usaha') == 'gizi-nutrisi' ? 'selected' : '' }}>Gizi & Nutrisi</option>
                                        <option value="kesehatan-lingkungan" {{ old('kategori_usaha') == 'kesehatan-lingkungan' ? 'selected' : '' }}>Kesehatan Lingkungan</option>
                                    </select>
                                    @error('kategori_usaha')
                                        <div class="invalid-feedback">{{ $message }}</div> {{-- Pesan error validasi kategori usaha --}}
                                    @enderror
                                </div>
                            </div>

                            {{-- Input untuk alamat toko --}}
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="alamat_toko" class="form-label">Alamat Toko <span class="text-danger">*</span></label>
                                    <textarea id="alamat_toko" 
                                              name="alamat_toko" 
                                              class="form-control @error('alamat_toko') is-invalid @enderror" 
                                              rows="3" 
                                              required 
                                              placeholder="Alamat lengkap toko fisik...">{{ old('alamat_toko') }}</textarea>
                                    @error('alamat_toko')
                                        <div class="invalid-feedback">{{ $message }}</div> {{-- Pesan error validasi alamat toko --}}
                                    @enderror
                                </div>

                                {{-- Input untuk nomor telepon toko --}}
                                <div class="col-md-4 mb-3">
                                    <label for="no_telepon" class="form-label">No. Telepon Toko</label>
                                    <input type="text" 
                                           id="no_telepon" 
                                           name="no_telepon" 
                                           class="form-control @error('no_telepon') is-invalid @enderror" 
                                           value="{{ old('no_telepon') }}" 
                                           placeholder="08123456789">
                                    @error('no_telepon')
                                        <div class="invalid-feedback">{{ $message }}</div> {{-- Pesan error validasi nomor telepon --}}
                                    @enderror
                                </div>
                            </div>

                            {{-- Input untuk deskripsi toko --}}
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="deskripsi_toko" class="form-label">Deskripsi Toko</label>
                                    <textarea id="deskripsi_toko" 
                                              name="deskripsi_toko" 
                                              class="form-control @error('deskripsi_toko') is-invalid @enderror" 
                                              rows="4" 
                                              placeholder="Ceritakan tentang toko Anda, produk yang akan dijual, dll...">{{ old('deskripsi_toko') }}</textarea>
                                    @error('deskripsi_toko')
                                        <div class="invalid-feedback">{{ $message }}</div> {{-- Pesan error validasi deskripsi toko --}}
                                    @enderror
                                </div>
                            </div>

                            {{-- Input untuk alasan permohonan --}}
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="alasan_permohonan" class="form-label">Alasan Permohonan <span class="text-danger">*</span></label>
                                    <textarea id="alasan_permohonan" 
                                              name="alasan_permohonan" 
                                              class="form-control @error('alasan_permohonan') is-invalid @enderror" 
                                              rows="4" 
                                              required 
                                              placeholder="Jelaskan mengapa Anda ingin membuka toko di OSS...">{{ old('alasan_permohonan') }}</textarea>
                                    @error('alasan_permohonan')
                                        <div class="invalid-feedback">{{ $message }}</div> {{-- Pesan error validasi alasan permohonan --}}
                                    @enderror
                                </div>
                            </div>

                            {{-- Informasi syarat dan ketentuan permohonan --}}
                            <div class="alert alert-warning">
                                <h6><i class="bi bi-info-circle"></i> Syarat dan Ketentuan</h6>
                                <ul class="mb-0">
                                    <li>Permohonan akan diproses dalam 1-3 hari kerja</li>
                                    <li>Admin berhak menyetujui atau menolak permohonan</li>
                                    <li>Jika disetujui, Anda akan mendapatkan akses admin toko</li>
                                    <li>Semua produk yang dijual harus sesuai dengan standar kesehatan</li>
                                    <li>Ingin membantu masyarakat dengan menyediakan obat-obatan berkualitas melalui platform Tukupedia</li>
                                </ul>
                            </div>

                            {{-- Tombol untuk membatalkan atau mengajukan permohonan --}}
                            <div class="text-end">
                                <a href="{{ route('home') }}" class="btn btn-secondary me-2">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Ajukan Permohonan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection 