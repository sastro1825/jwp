@extends('layouts.app')

@section('content')
{{-- Halaman Keranjang Belanja sesuai SRS untuk Kategori --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Keranjang Belanja</h1>
            <p class="text-muted">Periksa kategori yang akan dibeli sebelum checkout</p>
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

    @if($items->count() > 0)
        <div class="row">
            {{-- Tabel Keranjang Belanja --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-cart3"></i> Item dalam Keranjang ({{ $items->count() }} kategori)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">No.</th>
                                        <th width="40%">Nama Kategori dengan ID-nya</th>
                                        <th width="15%">Jumlah</th>
                                        <th width="20%">Harga</th>
                                        <th width="20%">Subtotal</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                {{-- Icon kategori --}}
                                                <div class="bg-light me-3 d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px; border-radius: 5px;">
                                                    <i class="bi bi-tags text-info" style="font-size: 1.5rem;"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $item->nama }}</h6>
                                                    <small class="text-muted">ID Kategori: KAT-{{ $item->kategori_id }}</small><br>
                                                    <small class="text-muted">Jenis: {{ $item->kategori->getCategoryTypeLabel() ?? 'Kategori' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{-- Form untuk update jumlah --}}
                                            <form action="{{ route('customer.keranjang.update', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <div class="input-group" style="width: 120px;">
                                                    <input type="number" 
                                                           name="jumlah" 
                                                           value="{{ $item->jumlah }}" 
                                                           min="1" 
                                                           max="10" 
                                                           class="form-control form-control-sm">
                                                    <button type="submit" class="btn btn-outline-primary btn-sm" title="Update">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">
                                                Rp {{ number_format($item->harga, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">
                                                Rp {{ number_format($item->jumlah * $item->harga, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            {{-- Tombol hapus dari keranjang --}}
                                            <form action="{{ route('customer.keranjang.hapus', $item->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Hapus kategori dari keranjang?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus dari keranjang">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary dan Checkout --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calculator"></i> Ringkasan Belanja
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Detail perhitungan sesuai SRS --}}
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal ({{ $items->sum('jumlah') }} item):</span>
                            <span class="fw-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Pajak (10%):</span>
                            <span class="fw-bold">Rp {{ number_format($pajak, 0, ',', '.') }}</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h5">Total belanja (termasuk pajak):</span>
                            <span class="h5 text-success fw-bold">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        {{-- Form Checkout sesuai SRS --}}
                        <form action="{{ route('customer.checkout') }}" method="POST">
                            @csrf
                            
                            {{-- Pilihan Metode Pembayaran sesuai SRS --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Metode Pembayaran:</label>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="metode_pembayaran" 
                                           value="prepaid" 
                                           id="prepaid" 
                                           checked>
                                    <label class="form-check-label" for="prepaid">
                                        <strong>Prepaid</strong><br>
                                        <small class="text-muted">Kartu Debit/Kredit/PayPal</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="metode_pembayaran" 
                                           value="postpaid" 
                                           id="postpaid">
                                    <label class="form-check-label" for="postpaid">
                                        <strong>Postpaid</strong><br>
                                        <small class="text-muted">Bayar di Tempat (COD)</small>
                                    </label>
                                </div>
                            </div>

                            {{-- Tombol Checkout --}}
                            <button type="submit" class="btn btn-success w-100 btn-lg">
                                <i class="bi bi-credit-card"></i> Checkout Sekarang
                            </button>
                        </form>

                        {{-- Info tambahan --}}
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-shield-check"></i>
                                Transaksi aman dan terjamin. Laporan PDF akan dikirim ke email Anda.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Info Kategori Summary --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-list-check"></i> Ringkasan Kategori
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $categoryTypes = $items->groupBy(function($item) {
                                return $item->kategori->category_type ?? 'lainnya';
                            });
                        @endphp
                        
                        @foreach($categoryTypes as $type => $itemsByType)
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ ucwords(str_replace('-', ' ', $type)) }}:</span>
                                <span class="badge bg-info">{{ $itemsByType->count() }}</span>
                            </div>
                        @endforeach
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <span><strong>Total Kategori:</strong></span>
                            <span class="badge bg-primary">{{ $items->count() }}</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-2">
                            <span><strong>Total Item:</strong></span>
                            <span class="badge bg-success">{{ $items->sum('jumlah') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Tombol lanjut belanja --}}
                <div class="mt-3">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-left"></i> Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>
    @else
        {{-- Tampilan jika keranjang kosong --}}
        <div class="text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 5rem; color: #ccc;"></i>
            <h4 class="mt-3">Keranjang Belanja Kosong</h4>
            <p class="text-muted">Anda belum menambahkan kategori ke keranjang.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="bi bi-bag-plus"></i> Mulai Belanja Kategori
            </a>
        </div>
    @endif
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
    
    // Add loading state untuk form update
    const updateForms = document.querySelectorAll('form[action*="keranjang/update"]');
    updateForms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        });
    });
    
    // Add loading state untuk checkout
    const checkoutForm = document.querySelector('form[action*="checkout"]');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function() {
            const submitBtn = checkoutForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
        });
    }
});
</script>
@endpush