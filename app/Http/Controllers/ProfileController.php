<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // Fungsi untuk menampilkan form edit profil pengguna
    public function edit(Request $request): View
    {
        // Ambil data pengguna yang sedang login
        $user = $request->user();
        
        // Ambil riwayat transaksi pengguna dengan relasi shippingOrder, diurutkan dari terbaru
        $transaksis = \App\Models\Transaksi::where('user_id', $user->id)
                                          ->with(['shippingOrder'])
                                          ->orderBy('created_at', 'desc')
                                          ->paginate(5); // Batasi 5 transaksi per halaman
        
        // Kembalikan view profile.edit dengan data pengguna dan transaksi
        return view('profile.edit', [
            'user' => $user,
            'transaksis' => $transaksis
        ]);
    }

    // Fungsi untuk memperbarui informasi profil pengguna
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Ambil data pengguna yang sedang login
        $user = $request->user();
        
        // Isi data pengguna dengan data yang telah divalidasi
        $user->fill($request->validated());

        // Reset verifikasi email jika email berubah
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Simpan perubahan data pengguna
        $user->save();

        // Redirect ke halaman edit profil dengan pesan sukses
        return Redirect::route('profile.edit')->with('success', 'Profile berhasil diperbarui!');
    }

    // Fungsi untuk menghapus akun pengguna
    public function destroy(Request $request): RedirectResponse
    {
        // Validasi password pengguna
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        // Ambil data pengguna yang sedang login
        $user = $request->user();

        // Logout pengguna
        Auth::logout();

        // Hapus akun pengguna
        $user->delete();

        // Invalidasi sesi dan regenerasi token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke halaman utama
        return Redirect::to('/');
    }
}