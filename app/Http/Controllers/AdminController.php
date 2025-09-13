<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Toko;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        $jumlahCustomer = User::where('role', 'customer')->count();
        $jumlahTokoPending = Toko::where('status', 'pending')->count();
        return view('admin.dashboard', compact('jumlahCustomer', 'jumlahTokoPending'));
    }

    public function manageCustomers()
    {
        $customers = User::where('role', 'customer')->get();
        $jumlahCustomer = User::where('role', 'customer')->count(); // Tambahan stats
        return view('admin.manage-customers', compact('customers', 'jumlahCustomer'));
    }

    public function manageKategori()
    {
        $kategoris = Kategori::all();
        return view('admin.manage-kategori', compact('kategoris'));
    }

    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'deskripsi' => 'nullable|string',
        ]);

        $kategoriData = $request->only(['nama', 'deskripsi']);

        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('kategori', 'public');
            $kategoriData['gambar'] = $path;
        }

        Kategori::create($kategoriData);

        return redirect()->route('admin.kategori')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);
        $request->validate([
            'nama' => 'required|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $kategori->update($request->only(['nama', 'deskripsi']));

        if ($request->hasFile('gambar')) {
            if ($kategori->gambar) {
                Storage::disk('public')->delete($kategori->gambar);
            }
            $path = $request->file('gambar')->store('kategori', 'public');
            $kategori->gambar = $path;
            $kategori->save();
        }

        return redirect()->route('admin.kategori')->with('success', 'Kategori berhasil diupdate.');
    }

    public function manageTokoRequests()
    {
        $tokos = Toko::with('user')->where('status', 'pending')->get();
        return view('admin.manage-toko', compact('tokos'));
    }

    public function approveToko($id)
    {
        $toko = Toko::findOrFail($id);
        $toko->status = 'approved';
        $toko->save();
        return redirect()->route('admin.toko.requests')->with('success', 'Toko disetujui.');
    }

    public function rejectToko($id)
    {
        $toko = Toko::findOrFail($id);
        $toko->status = 'rejected';
        $toko->save();
        return redirect()->route('admin.toko.requests')->with('success', 'Toko ditolak.');
    }
}