@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    {{-- Logo di sebelah kiri tulisan Selamat Datang --}}
                    <div class="d-flex align-items-center justify-content-center mb-4">
                        {{-- Logo Tukupedia di sebelah kiri --}}
                        <img src="{{ asset('images/Tukupedia.png') }}" 
                             alt="Tukupedia Logo" 
                             class="me-3"
                             style="width: 100px; height: 100px; object-fit: contain;">
                        {{-- Tulisan Selamat Datang di sebelah kanan logo --}}
                        <h2 class="mb-0">Selamat Datang</h2>
                    </div>
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3 text-start">
                            <label for="login" class="form-label">User ID:</label>
                            <input id="login" type="text" name="login" class="form-control" value="{{ old('login') }}" required autofocus>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="password" class="form-label">Password:</label>
                            <div class="input-group">
                                <input id="password" type="password" name="password" class="form-control" required>
                                <span class="input-group-text">
                                    <i class="bi bi-eye text-dark" id="togglePassword" style="cursor: pointer;"></i>
                                </span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">LOGIN</button>
                    </form>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Fungsi toggle mata untuk password login
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>
@endpush