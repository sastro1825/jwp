@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">FORM REGISTRASI</h2>
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="mb-3 text-start">
                            <label for="name" class="form-label">Username:</label>
                            <input id="name" type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="password" class="form-label">Password:</label>
                            <div class="input-group"> {{-- Input group untuk ikon di kanan --}}
                                <input id="password" type="password" name="password" class="form-control" required>
                                <span class="input-group-text"> {{-- Input group text untuk ikon --}}
                                    <i class="bi bi-eye text-dark" id="togglePassword" style="cursor: pointer;"></i> {{-- Ikon mata hitam, tengah kanan input --}}
                                </span>
                            </div>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="password_confirmation" class="form-label">Retype-Password:</label>
                            <div class="input-group"> {{-- Input group untuk ikon di kanan --}}
                                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                                <span class="input-group-text"> {{-- Input group text untuk ikon --}}
                                    <i class="bi bi-eye text-dark" id="toggleRetypePassword" style="cursor: pointer;"></i> {{-- Ikon mata hitam, tengah kanan input --}}
                                </span>
                            </div>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="email" class="form-label">E-mail:</label>
                            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="dob" class="form-label">Date of birth:</label>
                            <input id="dob" type="date" name="dob" class="form-control" value="{{ old('dob') }}" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label">Gender:</label>
                            <div>
                                <input type="radio" id="male" name="gender" value="male" {{ old('gender') == 'male' ? 'checked' : '' }}>
                                <label for="male">Male</label>
                                <input type="radio" id="female" name="gender" value="female" {{ old('gender') == 'female' ? 'checked' : '' }} class="ml-4">
                                <label for="female">Female</label>
                            </div>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="address" class="form-label">Address:</label>
                            <input id="address" type="text" name="address" class="form-control" value="{{ old('address') }}" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="city" class="form-label">City:</label>
                            <input id="city" type="text" name="city" class="form-control" value="{{ old('city') }}" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="contact_no" class="form-label">Contact no:</label>
                            <input id="contact_no" type="text" name="contact_no" class="form-control" value="{{ old('contact_no') }}" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="paypal_id" class="form-label">Pay-pal id:</label>
                            <input id="paypal_id" type="text" name="paypal_id" class="form-control" value="{{ old('paypal_id') }}" required>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success me-2">SUBMIT</button>
                            <button type="reset" class="btn btn-secondary">CLEAR</button>
                        </div>
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
    // Fungsi toggle mata untuk password di register
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
    // Fungsi toggle mata untuk retype-password di register
    document.getElementById('toggleRetypePassword').addEventListener('click', function() {
        const password = document.getElementById('password_confirmation');
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>
@endpush