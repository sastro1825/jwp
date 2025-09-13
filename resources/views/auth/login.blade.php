@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="card-title">LOGO</h2>
                    <h4 class="card-subtitle mb-4">Selamat datang di Toko Alat Kesehatan</h4>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3 text-start">
                            <label for="login" class="form-label">User ID:</label>
                            <input id="login" type="text" name="login" class="form-control" value="{{ old('login') }}" required autofocus>
                        </div>
                        <div class="mb-3 text-start position-relative">
                            <label for="password" class="form-label">Password:</label>
                            <input id="password" type="password" name="password" class="form-control" required>
                            <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3 text-dark" toggle="#password" style="cursor: pointer; font-size: 1.2em;"></i>
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
    document.querySelector('[toggle="#password"]').addEventListener('click', function() {
        const password = document.querySelector('#password');
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>
@endpush