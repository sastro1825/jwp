<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// Kelas untuk menangani permintaan login
class LoginRequest extends FormRequest
{
    // Fungsi untuk menentukan otorisasi pengguna
    public function authorize(): bool
    {
        // Mengizinkan semua pengguna untuk membuat permintaan
        return true;
    }

    // Aturan validasi untuk login menggunakan username atau email
    public function rules(): array
    {
        // Validasi input login dan password
        return [
            'login' => ['required', 'string', 'max:255'], // Input login bisa username atau email
            'password' => ['required', 'string'], // Input password wajib string
        ];
    }

    // Pesan kustom untuk error validasi
    public function messages(): array
    {
        // Kumpulan pesan error untuk validasi
        return [
            'login.required' => 'Username atau email wajib diisi.',
            'login.string' => 'Format username atau email tidak valid.',
            'login.max' => 'Username atau email maksimal 255 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Format password tidak valid.',
        ];
    }

    // Mencoba autentikasi berdasarkan kredensial
    public function authenticate(): void
    {
        // Pastikan tidak melebihi batas percobaan login
        $this->ensureIsNotRateLimited();

        // Ambil input login dan password
        $loginInput = $this->input('login');
        $password = $this->input('password');

        // Cek apakah input login adalah email
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        
        // Siapkan kredensial berdasarkan jenis input
        if ($isEmail) {
            // Gunakan email untuk login
            $credentials = [
                'email' => $loginInput,
                'password' => $password,
            ];
        } else {
            // Gunakan username untuk login
            $credentials = [
                'name' => $loginInput,
                'password' => $password,
            ];
        }

        // Coba autentikasi dengan kredensial
        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            // Tambah hitungan gagal login ke rate limiter
            RateLimiter::hit($this->throttleKey());

            // Lempar error validasi jika autentikasi gagal
            throw ValidationException::withMessages([
                'login' => [
                    $isEmail 
                        ? 'Email atau password salah.' 
                        : 'Username atau password salah.'
                ],
            ]);
        }

        // Bersihkan rate limiter jika login berhasil
        RateLimiter::clear($this->throttleKey());
    }

    // Cek apakah permintaan tidak dibatasi oleh rate limiter
    public function ensureIsNotRateLimited(): void
    {
        // Cek apakah sudah melebihi batas percobaan (5 kali)
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // Kirim event lockout
        event(new Lockout($this));

        // Hitung waktu tunggu dalam detik
        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Lempar error jika terlalu banyak percobaan
        throw ValidationException::withMessages([
            'login' => ['Terlalu banyak percobaan login. Coba lagi dalam ' . ceil($seconds / 60) . ' menit.'],
        ]);
    }

    // Generate kunci unik untuk rate limiting
    public function throttleKey(): string
    {
        // Buat kunci berdasarkan input login dan IP address
        return Str::transliterate(
            Str::lower($this->input('login')) . '|' . $this->ip()
        );
    }

    // Ambil nama field login untuk database
    public function getLoginUsername(): string
    {
        // Ambil input login
        $loginInput = $this->input('login');
        
        // Kembalikan field email atau name berdasarkan input
        return filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
    }

    // Cari user berdasarkan kredensial
    public function findUserByCredentials(): ?User
    {
        // Ambil input login
        $loginInput = $this->input('login');
        // Cek apakah input adalah email
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        // Cari user berdasarkan email atau username
        if ($isEmail) {
            return User::where('email', $loginInput)->first();
        } else {
            return User::where('name', $loginInput)->first();
        }
    }

    // Cek apakah user ada di database
    public function userExists(): bool
    {
        // Kembalikan true jika user ditemukan
        return $this->findUserByCredentials() !== null;
    }

    // Cek apakah input login adalah email
    public function isEmailLogin(): bool
    {
        // Kembalikan true jika input valid sebagai email
        return filter_var($this->input('login'), FILTER_VALIDATE_EMAIL) !== false;
    }

    // Validasi tambahan setelah aturan utama
    public function withValidator($validator): void
    {
        // Tambahkan validasi kustom
        $validator->after(function ($validator) {
            // Ambil input login
            $loginInput = $this->input('login');
            
            // Validasi format email jika mengandung @
            if (str_contains($loginInput, '@') && !filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                $validator->errors()->add('login', 'Format email tidak valid.');
            }
            
            // Validasi panjang username jika bukan email
            if (!filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                if (strlen($loginInput) < 3) {
                    $validator->errors()->add('login', 'Username minimal 3 karakter.');
                }
                if (strlen($loginInput) > 50) {
                    $validator->errors()->add('login', 'Username maksimal 50 karakter.');
                }
            }
        });
    }

    // Ambil kredensial untuk autentikasi
    public function getCredentials(): array
    {
        // Ambil input login dan password
        $loginInput = $this->input('login');
        $password = $this->input('password');
        // Cek apakah input adalah email
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        
        // Kembalikan kredensial berdasarkan jenis input
        if ($isEmail) {
            return [
                'email' => $loginInput,
                'password' => $password,
            ];
        } else {
            return [
                'name' => $loginInput,
                'password' => $password,
            ];
        }
    }
}