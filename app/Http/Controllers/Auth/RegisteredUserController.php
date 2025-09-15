<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * Support untuk field tambahan: dob, gender, address, city, contact_no, paypal_id
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi input registrasi dengan field tambahan
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'dob' => ['required', 'date', 'before:today'], // Date of birth wajib dan harus sebelum hari ini
            'gender' => ['required', 'in:male,female'], // Gender wajib pilih male atau female
            'address' => ['required', 'string', 'max:500'], // Alamat wajib diisi
            'city' => ['required', 'string', 'max:100'], // Kota wajib diisi
            'contact_no' => ['required', 'string', 'max:20'], // No HP wajib diisi
            'paypal_id' => ['required', 'string', 'max:100'], // PayPal ID wajib diisi
        ]);

        // Buat user baru dengan field tambahan
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'dob' => $request->dob,
            'gender' => $request->gender,
            'address' => $request->address,
            'city' => $request->city,
            'contact_no' => $request->contact_no,
            'paypal_id' => $request->paypal_id,
            'role' => 'customer', // Default role adalah customer
        ]);

        // Trigger event registered
        event(new Registered($user));

        // Login otomatis setelah registrasi
        Auth::login($user);

        // Redirect ke halaman utama untuk customer
        return redirect(route('home', absolute: false));
    }
}