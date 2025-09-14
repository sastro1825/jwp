@extends('layouts.app')

@section('content')
{{-- Form Edit Customer --}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Edit Customer</h1>
            <p class="text-muted">Perbarui data customer: <strong>{{ $customer->name }}</strong></p>
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
            {{-- Form Edit Customer --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person-gear"></i> Form Edit Customer
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            {{-- Nama Customer --}}
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

                            {{-- Email Customer --}}
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
                            {{-- Alamat Customer --}}
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
                            {{-- Kota Customer --}}
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

                            {{-- No HP Customer --}}
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

                        {{-- Info Customer yang tidak bisa diedit --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle"></i> Informasi Customer</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>ID Customer:</strong> {{ $customer->id }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Role:</strong> {{ ucfirst($customer->role) }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Terdaftar:</strong> {{ $customer->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                    @if($customer->dob)
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <strong>Tanggal Lahir:</strong> {{ $customer->dob->format('d/m/Y') }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Gender:</strong> {{ ucfirst($customer->gender ?? '-') }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>PayPal ID:</strong> {{ $customer->paypal_id ?? '-' }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Submit --}}
                        <div class="text-end">
                            <a href="{{ route('admin.customers') }}" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
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