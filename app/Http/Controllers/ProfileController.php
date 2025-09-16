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
    /**
     * Display the user's profile form - DIPERBAIKI: Tampilkan info lengkap user
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        // Ambil riwayat transaksi user untuk ditampilkan di profile
        $transaksis = \App\Models\Transaksi::where('user_id', $user->id)
                                          ->with(['shippingOrder'])
                                          ->orderBy('created_at', 'desc')
                                          ->paginate(5); // Pagination 5 item per halaman
        
        return view('profile.edit', [
            'user' => $user,
            'transaksis' => $transaksis // Pass data transaksi ke view
        ]);
    }

    /**
     * Update the user's profile information - DIPERBAIKI: Update semua field profile
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Update data user dengan semua field yang di-validate
        $user->fill($request->validated());

        // Reset email verification jika email berubah
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profile berhasil diperbarui!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}