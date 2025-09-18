@extends('layouts.app')

@section('content')
    {{-- Kontainer utama untuk form edit customer --}}
    <div class="container">
        <div class="row">
            <div class="col-12">
                {{-- Judul halaman edit customer --}}
                <h1>Edit Customer</h1>
                {{-- Menampilkan nama customer yang sedang diedit --}}
                <p class="text-muted">Perbarui data customer: <strong>{{ $customer->name }}</strong></p>
            </div>
        </div>

        {{-- Pesan sukses jika ada --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                {{-- Tombol untuk menutup alert --}}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Bagian untuk menampilkan error validasi --}}
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="bi bi-exclamation-triangle"></i> Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Bagian utama form edit customer --}}
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    {{-- Header kartu untuk form --}}
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person-gear"></i> Form Edit Customer
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Form untuk memperbarui data customer --}}
                        <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST" id="updateCustomerForm">
                            @csrf
                            @method('PATCH')

                            <div class="row">
                                {{-- Input nama customer --}}
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nama Customer <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $customer->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Input email customer --}}
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $customer->email) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                {{-- Input alamat customer --}}
                                <div class="col-md-12 mb-3">
                                    <label for="address" class="form-label">Alamat</label>
                                    <textarea id="address" 
                                              name="address" 
                                              class="form-control @error('address') is-invalid @enderror" 
                                              rows="3">{{ old('address', $customer->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                {{-- Input kota customer --}}
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">Kota</label>
                                    <input type="text" 
                                           id="city" 
                                           name="city" 
                                           class="form-control @error('city') is-invalid @enderror" 
                                           value="{{ old('city', $customer->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Input nomor HP customer --}}
                                <div class="col-md-6 mb-3">
                                    <label for="contact_no" class="form-label">No. HP</label>
                                    <input type="text" 
                                           id="contact_no" 
                                           name="contact_no" 
                                           class="form-control @error('contact_no') is-invalid @enderror" 
                                           value="{{ old('contact_no', $customer->contact_no) }}">
                                    @error('contact_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                {{-- Input role customer dengan validasi --}}
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Role Customer <span class="text-danger">*</span></label>
                                    <select id="role" 
                                            name="role" 
                                            class="form-control @error('role') is-invalid @enderror" 
                                            required>
                                        <option value="">Pilih Role...</option>
                                        <option value="customer" {{ old('role', $customer->role) == 'customer' ? 'selected' : '' }}>Customer</option>
                                        <option value="pemilik_toko" {{ old('role', $customer->role) == 'pemilik_toko' ? 'selected' : '' }}>Pemilik Toko</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Informasi customer yang tidak dapat diedit --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6><i class="bi bi-info-circle"></i> Informasi Customer</h6>
                                        <div class="row">
                                            {{-- Menampilkan ID customer --}}
                                            <div class="col-md-4">
                                                <strong>ID Customer:</strong> {{ $customer->id }}
                                            </div>
                                            {{-- Menampilkan peran customer --}}
                                            <div class="col-md-4">
                                                <strong>Role:</strong> {{ ucfirst($customer->role) }}
                                            </div>
                                            {{-- Menampilkan tanggal registrasi --}}
                                            <div class="col-md-4">
                                                <strong>Terdaftar:</strong> {{ $customer->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                        @if($customer->dob)
                                            <div class="row mt-2">
                                                {{-- Menampilkan tanggal lahir --}}
                                                <div class="col-md-4">
                                                    <strong>Tanggal Lahir:</strong> {{ $customer->dob->format('d/m/Y') }}
                                                </div>
                                                {{-- Menampilkan jenis kelamin --}}
                                                <div class="col-md-4">
                                                    <strong>Gender:</strong> {{ ucfirst($customer->gender ?? '-') }}
                                                </div>
                                                {{-- Menampilkan ID PayPal --}}
                                                <div class="col-md-4">
                                                    <strong>PayPal ID:</strong> {{ $customer->paypal_id ?? '-' }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol untuk submit dan batal --}}
                            <div class="text-end">
                                <a href="{{ route('admin.customers') }}" class="btn btn-secondary me-2">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-check-circle"></i> Perbarui Customer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Menangani submit form dengan loading state
document.getElementById('updateCustomerForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    
    // Menampilkan loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
    
    // Reset tombol setelah 10 detik jika masih loading
    setTimeout(() => {
        if (submitBtn.disabled) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Perbarui Customer';
        }
    }, 10000);
});

// Auto hide alerts setelah 5 detik
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });
});
</script>
@endpush