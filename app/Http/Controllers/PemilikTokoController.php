<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TokoKategori;
use App\Models\ShippingOrder;
use App\Models\Toko;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
     * Store Kategori Baru untuk Pemilik Toko - PERBAIKAN PATH UPLOAD
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

            // PERBAIKAN: Handle upload gambar dengan validasi yang lebih ketat
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                
                // Pastikan file valid dan bisa dibaca
                if ($file->isValid()) {
                    $filename = time() . '_' . Str::slug($validated['nama']) . '.' . $file->getClientOriginalExtension();
                    
                    // Simpan dengan path yang eksplisit
                    $path = $file->storeAs('toko-kategoris', $filename, 'public');
                    
                    // Verifikasi file tersimpan
                    if (Storage::disk('public')->exists($path)) {
                        $validated['gambar'] = $path;
                        
                        Log::info('Image uploaded successfully', [
                            'filename' => $filename,
                            'path' => $path,
                            'size' => $file->getSize(),
                            'mime' => $file->getMimeType()
                        ]);
                    } else {
                        Log::error('Failed to verify uploaded file');
                        return redirect()->back()->with('error', 'Gagal menyimpan gambar.');
                    }
                } else {
                    return redirect()->back()->with('error', 'File gambar tidak valid.');
                }
            }

            $validated['toko_id'] = $toko->id;
            TokoKategori::create($validated);

            return redirect()->route('pemilik-toko.kategori')->with('success', 'Kategori berhasil ditambahkan ke toko Anda.');
            
        } catch (\Exception $e) {
            Log::error('Error creating toko kategori: ' . $e->getMessage());
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
                $filename = time() . '_' . Str::slug($validated['nama']) . '.' . $request->file('gambar')->getClientOriginalExtension();
                $path = $request->file('gambar')->storeAs('toko-kategoris', $filename, 'public');
                if (Storage::disk('public')->exists($path)) {
                    $validated['gambar'] = $path;
                    Log::info('Image updated successfully', [
                        'filename' => $filename,
                        'path' => $path,
                        'size' => $request->file('gambar')->getSize(),
                        'mime' => $request->file('gambar')->getMimeType()
                    ]);
                } else {
                    Log::error('Failed to verify updated file');
                    return redirect()->back()->with('error', 'Gagal menyimpan gambar.');
                }
            }

            $kategori->update($validated);

            return redirect()->route('pemilik-toko.kategori')->with('success', 'Kategori berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('Error updating toko kategori: ' . $e->getMessage());
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
            Log::error('Error deleting toko kategori: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    /**
     * Kelola Shipping Orders untuk item yang berasal dari toko ini - DIPERBAIKI LOGIC
     */
    public function manageShippingOrders()
    {
        $user = auth()->user();
        $toko = $user->toko;
        
        if (!$toko) {
            return redirect()->route('pemilik-toko.dashboard')
                ->with('error', 'Anda belum memiliki toko aktif.');
        }
        
        // Ambil shipping orders untuk transaksi yang mengandung item dari toko ini
        $shippingOrders = ShippingOrder::with(['transaksi.user'])
            ->whereHas('transaksi.detailTransaksi', function($query) use ($toko) {
                // Filter berdasarkan item yang berasal dari toko ini
                $query->where('item_type', 'toko_kategori')
                      ->where('nama_item', 'LIKE', '%' . $toko->nama . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('pemilik-toko.manage-shipping', compact('shippingOrders', 'toko'));
    }

    /**
     * Update status shipping order untuk pemilik toko - METHOD BARU
     */
    public function updateShippingStatus(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $toko = $user->toko;
            
            if (!$toko) {
                return redirect()->back()->with('error', 'Anda belum memiliki toko aktif.');
            }

            // Ambil shipping order yang terkait dengan toko ini
            $shipping = ShippingOrder::whereHas('transaksi.detailTransaksi', function($query) use ($toko) {
                $query->where('item_type', 'toko_kategori')
                      ->where('nama_item', 'LIKE', '%' . $toko->nama . '%');
            })->findOrFail($id);
            
            $validated = $request->validate([
                'status' => 'required|in:pending,shipped,delivered,cancelled',
                'courier' => 'nullable|string|max:100',
                'shipped_date' => 'nullable|date',
                'delivered_date' => 'nullable|date',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Update data shipping
            $shipping->update([
                'status' => $validated['status'],
                'courier' => $validated['courier'] ?? $shipping->courier,
                'shipped_date' => $validated['shipped_date'] ? \Carbon\Carbon::parse($validated['shipped_date']) : $shipping->shipped_date,
                'delivered_date' => $validated['delivered_date'] ? \Carbon\Carbon::parse($validated['delivered_date']) : $shipping->delivered_date,
                'notes' => $validated['notes'] ?? $shipping->notes,
            ]);

            // Update status transaksi berdasarkan status shipping
            if ($validated['status'] === 'delivered') {
                $shipping->transaksi->update(['status' => 'completed']);
            } elseif ($validated['status'] === 'cancelled') {
                $shipping->transaksi->update(['status' => 'cancelled']);
            } elseif ($validated['status'] === 'shipped') {
                $shipping->transaksi->update(['status' => 'processing']);
            }

            return redirect()->route('pemilik-toko.shipping')->with('success', 'Status pengiriman berhasil diupdate.');
            
        } catch (\Exception $e) {
            Log::error('Error updating shipping status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate status pengiriman: ' . $e->getMessage());
        }
    }
}