<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> {{-- Menentukan encoding karakter UTF-8 --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> {{-- Mengatur responsivitas tampilan --}}
    <title>Tukupedia</title> {{-- Judul halaman --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> {{-- Memuat CSS Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet"> {{-- Memuat ikon Bootstrap --}}
    
    @stack('styles') {{-- Memuat CSS tambahan dari view --}}
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light"> {{-- Navigasi utama dengan Bootstrap --}}
        <div class="container"> {{-- Kontainer untuk tata letak responsif --}}
            <a class="navbar-brand" href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('admin.dashboard') : route('home') }}">Tukupedia</a> {{-- Logo atau nama brand, mengarahkan ke dashboard admin atau halaman utama --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"> {{-- Tombol toggle untuk menu responsif --}}
                <span class="navbar-toggler-icon"></span> {{-- Ikon toggle --}}
            </button>
            <div class="collapse navbar-collapse" id="navbarNav"> {{-- Konten navigasi yang bisa diperluas/ditutup --}}
                <ul class="navbar-nav ms-auto"> {{-- Daftar navigasi di sisi kanan --}}
                    @guest {{-- Kondisi untuk pengguna yang belum login --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a> {{-- Tautan ke halaman login --}}
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a> {{-- Tautan ke halaman registrasi --}}
                        </li>
                    @else {{-- Kondisi untuk pengguna yang sudah login --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">{{ Auth::user()->name }}</a> {{-- Menu dropdown dengan nama pengguna --}}
                            <ul class="dropdown-menu"> {{-- Daftar opsi dropdown --}}
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li> {{-- Tautan ke halaman edit profil --}}
                                <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li> {{-- Tautan logout dengan submit form --}}
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;"> {{-- Form untuk logout --}}
                                    @csrf {{-- Token CSRF untuk keamanan --}}
                                </form>
                            </ul>
                        </li>
                    @endguest {{-- Akhir kondisi guest --}}
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4"> {{-- Konten utama dengan margin atas --}}
        @yield('content') {{-- Menampilkan konten dari view lain --}}
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> {{-- Memuat JavaScript Bootstrap --}}
    @stack('scripts') {{-- Memuat script tambahan dari view --}}
</body>
</html>