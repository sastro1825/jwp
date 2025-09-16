<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request - support login dengan username atau email
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'], // Field login bisa username atau email
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get custom messages for validation errors
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Username atau email wajib diisi.',
            'login.string' => 'Format username atau email tidak valid.',
            'login.max' => 'Username atau email maksimal 255 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Format password tidak valid.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials
     * Support login dengan username (field name) atau email
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        // Cek rate limiting terlebih dahulu
        $this->ensureIsNotRateLimited();

        $loginInput = $this->input('login');
        $password = $this->input('password');

        // Tentukan apakah input login adalah email atau username
        // Jika mengandung @ dan format email valid, anggap sebagai email
        // Jika tidak, anggap sebagai username (field name di database)
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        
        if ($isEmail) {
            // Login menggunakan email
            $credentials = [
                'email' => $loginInput,
                'password' => $password,
            ];
        } else {
            // Login menggunakan username (field name)
            $credentials = [
                'name' => $loginInput,
                'password' => $password,
            ];
        }

        // Attempt authentication dengan credentials yang sesuai
        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            // Jika gagal login, increment rate limiter
            RateLimiter::hit($this->throttleKey());

            // Throw validation exception dengan pesan error yang sesuai
            throw ValidationException::withMessages([
                'login' => [
                    $isEmail 
                        ? 'Email atau password yang Anda masukkan salah.' 
                        : 'Username atau password yang Anda masukkan salah.'
                ],
            ]);
        }

        // Jika berhasil login, clear rate limiter
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited
     * Mencegah brute force attack dengan rate limiting
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // Cek apakah sudah mencapai limit maksimal percobaan login
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // Trigger event lockout
        event(new Lockout($this));

        // Hitung berapa detik lagi bisa mencoba login
        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Throw validation exception dengan pesan rate limit
        throw ValidationException::withMessages([
            'login' => [
                'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . 
                ceil($seconds / 60) . ' menit.'
            ],
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request
     * Generate key unik untuk rate limiting berdasarkan input dan IP
     */
    public function throttleKey(): string
    {
        // Buat key unik berdasarkan input login dan IP address
        // Transliterate untuk handle karakter khusus
        return Str::transliterate(
            Str::lower($this->input('login')) . '|' . $this->ip()
        );
    }

    /**
     * Get the login username to be used by the controller
     * Method untuk mendapatkan field name yang sesuai untuk database
     */
    public function getLoginUsername(): string
    {
        $loginInput = $this->input('login');
        
        // Return field name yang sesuai untuk database
        return filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
    }

    /**
     * Get the user instance for the given credentials
     * Method untuk mendapatkan user berdasarkan credentials (nama method diubah untuk hindari konflik)
     */
    public function findUserByCredentials(): ?User
    {
        $loginInput = $this->input('login');
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            return User::where('email', $loginInput)->first();
        } else {
            return User::where('name', $loginInput)->first();
        }
    }

    /**
     * Determine if the user exists in the database
     * Method untuk cek apakah user ada di database
     */
    public function userExists(): bool
    {
        return $this->findUserByCredentials() !== null;
    }

    /**
     * Check if the login input is an email
     * Method untuk cek apakah input adalah email
     */
    public function isEmailLogin(): bool
    {
        return filter_var($this->input('login'), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Get validation rules with custom logic
     * Method untuk validasi tambahan jika diperlukan
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $loginInput = $this->input('login');
            
            // Validasi format email jika input mengandung @
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

    /**
     * Get the credentials for authentication
     * Method untuk mendapatkan credentials yang sesuai
     */
    public function getCredentials(): array
    {
        $loginInput = $this->input('login');
        $password = $this->input('password');
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        
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