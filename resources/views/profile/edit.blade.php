@extends('layouts.app')

@section('content')
{{-- Halaman Profile Management Customer --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Profile Saya</h1>
            <p class="text-muted">Kelola informasi akun dan lihat riwayat transaksi Anda</p>
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
        {{-- Informasi Akun (Kolom Kiri) --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle"></i> Informasi Akun
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            {{-- Nama --}}
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Tanggal Lahir --}}
                            <div class="col-md-6 mb-3">
                                <label for="dob" class="form-label">Tanggal Lahir</label>
                                <input type="date" 
                                       id="dob" 
                                       name="dob" 
                                       class="form-control @error('dob') is-invalid @enderror" 
                                       value="{{ old('dob', $user->dob ? $user->dob->format('Y-m-d') : '') }}">
                                @error('dob')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Gender --}}
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" 
                                        name="gender" 
                                        class="form-control @error('gender') is-invalid @enderror">
                                    <option value="">Pilih Gender...</option>
                                    <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Alamat --}}
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="address" class="form-label">Alamat</label>
                                <textarea id="address" 
                                          name="address" 
                                          class="form-control @error('address') is-invalid @enderror" 
                                          rows="3" 
                                          placeholder="Masukkan alamat lengkap...">{{ old('address', $user->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Kota --}}
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">Kota</label>
                                <input type="text" 
                                       id="city" 
                                       name="city" 
                                       class="form-control @error('city') is-invalid @enderror" 
                                       value="{{ old('city', $user->city) }}" 
                                       placeholder="Contoh: Surabaya">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- No HP --}}
                            <div class="col-md-6 mb-3">
                                <label for="contact_no" class="form-label">No. HP</label>
                                <input type="text" 
                                       id="contact_no" 
                                       name="contact_no" 
                                       class="form-control @error('contact_no') is-invalid @enderror" 
                                       value="{{ old('contact_no', $user->contact_no) }}" 
                                       placeholder="Contoh: 086289121222">
                                @error('contact_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- PayPal ID --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="paypal_id" class="form-label">PayPal ID</label>
                                <input type="text" 
                                       id="paypal_id" 
                                       name="paypal_id" 
                                       class="form-control @error('paypal_id') is-invalid @enderror" 
                                       value="{{ old('paypal_id', $user->paypal_id) }}" 
                                       placeholder="Contoh: user@paypal.com">
                                @error('paypal_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Info Bergabung --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bergabung</label>
                                <div class="form-control-plaintext">
                                    <strong>{{ $user->created_at->format('d/m/Y') }}</strong>
                                    <small class="text-muted d-block">Member sejak {{ $user->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Update --}}
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Quick Stats (Kolom Kanan) --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up"></i> Statistik Akun
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h4 class="text-primary">{{ $transaksis->total() }}</h4>
                            <small class="text-muted">Total Transaksi</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-success">
                                @php
                                    $totalSpent = \App\Models\Transaksi::where('user_id', $user->id)->sum('total');
                                @endphp
                                {{ $totalSpent > 0 ? 'Rp ' . number_format($totalSpent, 0, ',', '.') : 'Rp 0' }}
                            </h4>
                            <small class="text-muted">Total Belanja</small>
                        </div>
                    </div>

                    {{-- Quick Action Buttons --}}
                    <div class="d-grid gap-2">
                        <a href="{{ route('customer.order.history') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-clock-history"></i> Riwayat Pesanan
                        </a>
                        <a href="{{ route('customer.keranjang') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-cart3"></i> Lihat Keranjang
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-house"></i> Kembali Berbelanja
                        </a>
                    </div>
                </div>
            </div>

            {{-- Security Card --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-shield-check"></i> Keamanan
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Untuk keamanan akun, pastikan password Anda kuat dan unik.</p>
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="bi bi-key"></i> Ganti Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Transaksi Singkat --}}
    @if($transaksis->count() > 0)
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-receipt"></i> Riwayat Transaksi Terbaru
            </h5>
            <a href="{{ route('customer.order.history') }}" class="btn btn-sm btn-outline-primary">
                Lihat Semua
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaksis->take(3) as $transaksi)
                        <tr>
                            <td><strong>#{{ $transaksi->id }}</strong></td>
                            <td>{{ $transaksi->created_at->format('d/m/Y') }}</td>
                            <td>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
                            <td>
                                @if($transaksi->shippingOrder)
                                    @if($transaksi->shippingOrder->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($transaksi->shippingOrder->status === 'shipped')
                                        <span class="badge bg-info">Dikirim</span>
                                    @elseif($transaksi->shippingOrder->status === 'delivered')
                                        <span class="badge bg-success">Sampai</span>
                                    @else
                                        <span class="badge bg-danger">Batal</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">No Shipping</span>
                                @endif
                            </td>
                            <td>
                                @if($transaksi->pdf_path)
                                    <a href="{{ route('customer.download.laporan', $transaksi->id) }}" 
                                       class="btn btn-xs btn-outline-primary" 
                                       title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Modal Change Password --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ganti Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Fitur ganti password akan tersedia di update selanjutnya.</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Untuk sementara, hubungi admin jika perlu mengganti password.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-hide alerts after 5 seconds
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