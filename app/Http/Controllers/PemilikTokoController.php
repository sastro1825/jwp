<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TokoKategori;
use App\Models\ShippingOrder;
use App\Models\Toko;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PemilikTokoController extends Controller
{
    /**
     * Kelola Kategori khusus untuk pemilik toko (database terpisah)
     */
    public function manageKategori()
    {
        $toko = auth()->user()->toko;
        
        if (!$toko) {
            return redirect()->route('pemilik-toko.dashboard')->with('error', 'Anda belum memiliki toko.');
        }

        // Ambil kategori milik toko sendiri
        $kategoris = TokoKategori::where('toko_id', $toko->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('pemilik-toko.manage-kategori', compact('kategoris', 'toko'));
    }

    /**
     * Store Kategori Baru untuk Pemilik Toko
     */
    public function storeKategori(Request $request)
    {
        try {
            $toko = auth()->user()->toko;
            
            if (!$toko) {
                return redirect()->back()->with('error', 'Anda belum memiliki toko.');
            }

            $validated = $request->validate([
                'nama' => 'required|string|max:100',
                'deskripsi' => 'nullable|string|max:500',
                'harga' => 'required|numeric|min:0',
                'category_type' => 'required|string|in:alat-kesehatan,obat-obatan,suplemen-kesehatan,perawatan-kecantikan,kesehatan-pribadi',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            // Handle upload gambar
            if ($request->hasFile('gambar')) {
                $path = $request->file('gambar')->store('toko-kategoris', 'public');
                $validated['gambar'] = $path;
            }

            $validated['toko_id'] = $toko->id;

            TokoKategori::create($validated);

            return redirect()->route('pemilik-toko.kategori')->with('success', 'Kategori berhasil ditambahkan ke toko Anda.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }
    }

    /**
     * Edit Kategori untuk Pemilik Toko
     */
    public function editKategori($id)
    {
        $toko = auth()->user()->toko;
        $kategori = TokoKategori::where('toko_id', $toko->id)->findOrFail($id);
        
        return view('pemilik-toko.edit-kategori', compact('kategori', 'toko'));
    }

    /**
     * Update Kategori untuk Pemilik Toko
     */
    public function updateKategori(Request $request, $id)
    {
        try {
            $toko = auth()->user()->toko;
            $kategori = TokoKategori::where('toko_id', $toko->id)->findOrFail($id);
            
            $validated = $request->validate([
                'nama' => 'required|string|max:100',
                'deskripsi' => 'nullable|string|max:500',
                'harga' => 'required|numeric|min:0',
                'category_type' => 'required|string|in:alat-kesehatan,obat-obatan,suplemen-kesehatan,perawatan-kecantikan,kesehatan-pribadi',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            // Handle upload gambar baru
            if ($request->hasFile('gambar')) {
                if ($kategori->gambar) {
                    Storage::disk('public')->delete($kategori->gambar);
                }
                $path = $request->file('gambar')->store('toko-kategoris', 'public');
                $validated['gambar'] = $path;
            }

            $kategori->update($validated);

            return redirect()->route('pemilik-toko.kategori')->with('success', 'Kategori berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate kategori: ' . $e->getMessage());
        }
    }

    /**
     * Delete Kategori untuk Pemilik Toko
     */
    public function deleteKategori($id)
    {
        try {
            $toko = auth()->user()->toko;
            $kategori = TokoKategori::where('toko_id', $toko->id)->findOrFail($id);
            
            // Hapus gambar jika ada
            if ($kategori->gambar) {
                Storage::disk('public')->delete($kategori->gambar);
            }
            
            $kategori->delete();

            return redirect()->route('pemilik-toko.kategori')->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    /**
     * Kelola Shipping Orders untuk Pemilik Toko
     */
    public function manageShippingOrders()
    {
        $user = auth()->user();
        $toko = $user->toko;
        
        if ($toko) {
            $shippingOrders = ShippingOrder::with(['transaksi.user'])
                ->whereHas('transaksi', function($query) use ($user) {
                    $query->where('user_id', '!=', null);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            $shippingOrders = collect()->paginate(15);
        }
        
        return view('pemilik-toko.manage-shipping', compact('shippingOrders', 'toko'));
    }
}