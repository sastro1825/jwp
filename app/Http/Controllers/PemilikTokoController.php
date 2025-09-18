<?php

namespace App\Http\Controllers;

// Impor kelas yang diperlukan
use Illuminate\Http\Request;
use App\Models\TokoKategori;
use App\Models\ShippingOrder;
use App\Models\Toko;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Kelas untuk mengelola fungsi pemilik toko
class PemilikTokoController extends Controller
{
    // Fungsi untuk mengelola kategori toko
    public function manageKategori()
    {
        // Ambil data toko dari pengguna yang login
        $toko = auth()->user()->toko;
        
        // Periksa apakah toko ada
        if (!$toko) {
            return redirect()->route('pemilik-toko.dashboard')->with('error', 'Anda belum memiliki toko.');
        }

        // Ambil daftar kategori milik toko, urutkan berdasarkan tanggal pembuatan
        $kategoris = TokoKategori::where('toko_id', $toko->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Tampilkan view manajemen kategori
        return view('pemilik-toko.manage-kategori', compact('kategoris', 'toko'));
    }

    // Fungsi untuk menyimpan kategori baru
    public function storeKategori(Request $request)
    {
        try {
            // Ambil data toko dari pengguna yang login
            $toko = auth()->user()->toko;
            
            // Periksa apakah toko ada
            if (!$toko) {
                return redirect()->back()->with('error', 'Anda belum memiliki toko.');
            }

            // Validasi input dari request
            $validated = $request->validate([
                'nama' => 'required|string|max:100', // Nama kategori wajib diisi, maksimum 100 karakter
                'deskripsi' => 'nullable|string|max:500', // Deskripsi opsional, maksimum 500 karakter
                'harga' => 'required|numeric|min:0', // Harga wajib diisi, harus numerik dan tidak negatif
                'category_type' => 'required|string|in:alat-kesehatan,obat-obatan,suplemen-kesehatan,perawatan-kecantikan,kesehatan-pribadi', // Tipe kategori wajib diisi, sesuai opsi yang ditentukan
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048', // Gambar opsional, harus berupa file gambar dengan ukuran maksimum 2MB
            ]);

            // Proses upload gambar jika ada
            if ($request->hasFile('gambar')) {
                // Ambil file gambar dari request
                $file = $request->file('gambar');
                
                // Periksa apakah file valid
                if ($file->isValid()) {
                    // Buat nama file unik menggunakan timestamp dan nama kategori
                    $filename = time() . '_' . Str::slug($validated['nama']) . '.' . $file->getClientOriginalExtension();
                    
                    // Simpan file ke direktori 'toko-kategoris' di disk public
                    $path = $file->storeAs('toko-kategoris', $filename, 'public');
                    
                    // Verifikasi apakah file tersimpan
                    if (Storage::disk('public')->exists($path)) {
                        // Simpan path gambar ke data validasi
                        $validated['gambar'] = $path;
                        
                        // Catat log informasi upload gambar
                        Log::info('Image uploaded successfully', [
                            'filename' => $filename,
                            'path' => $path,
                            'size' => $file->getSize(),
                            'mime' => $file->getMimeType()
                        ]);
                    } else {
                        // Catat log kesalahan jika file tidak tersimpan
                        Log::error('Failed to verify uploaded file');
                        return redirect()->back()->with('error', 'Gagal menyimpan gambar.');
                    }
                } else {
                    // Kembalikan pesan error jika file tidak valid
                    return redirect()->back()->with('error', 'File gambar tidak valid.');
                }
            }

            // Tambahkan ID toko ke data validasi
            $validated['toko_id'] = $toko->id;
            // Simpan kategori baru ke database
            TokoKategori::create($validated);

            // Redirect dengan pesan sukses
            return redirect()->route('pemilik-toko.kategori')->with('success', 'Kategori berhasil ditambahkan ke toko Anda.');
            
        } catch (\Exception $e) {
            // Catat log kesalahan jika terjadi exception
            Log::error('Error creating toko kategori: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }
    }

    // Fungsi untuk menampilkan form edit kategori
    public function editKategori($id)
    {
        // Ambil data toko dari pengguna yang login
        $toko = auth()->user()->toko;
        // Ambil data kategori berdasarkan ID dan toko
        $kategori = TokoKategori::where('toko_id', $toko->id)->findOrFail($id);
        
        // Tampilkan view edit kategori
        return view('pemilik-toko.edit-kategori', compact('kategori', 'toko'));
    }

    // Fungsi untuk memperbarui kategori
    public function updateKategori(Request $request, $id)
    {
        try {
            // Ambil data toko dari pengguna yang login
            $toko = auth()->user()->toko;
            // Ambil data kategori berdasarkan ID dan toko
            $kategori = TokoKategori::where('toko_id', $toko->id)->findOrFail($id);
            
            // Validasi input dari request
            $validated = $request->validate([
                'nama' => 'required|string|max:100', // Nama kategori wajib diisi
                'deskripsi' => 'nullable|string|max:500', // Deskripsi opsional
                'harga' => 'required|numeric|min:0', // Harga wajib diisi, tidak negatif
                'category_type' => 'required|string|in:alat-kesehatan,obat-obatan,suplemen-kesehatan,perawatan-kecantikan,kesehatan-pribadi', // Tipe kategori wajib diisi
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048', // Gambar opsional, maksimum 2MB
            ]);

            // Proses upload gambar baru jika ada
            if ($request->hasFile('gambar')) {
                // Hapus gambar lama jika ada
                if ($kategori->gambar) {
                    Storage::disk('public')->delete($kategori->gambar);
                }
                // Buat nama file baru
                $filename = time() . '_' . Str::slug($validated['nama']) . '.' . $request->file('gambar')->getClientOriginalExtension();
                // Simpan gambar baru
                $path = $request->file('gambar')->storeAs('toko-kategoris', $filename, 'public');
                // Verifikasi apakah file tersimpan
                if (Storage::disk('public')->exists($path)) {
                    // Simpan path gambar ke data validasi
                    $validated['gambar'] = $path;
                    // Catat log informasi update gambar
                    Log::info('Image updated successfully', [
                        'filename' => $filename,
                        'path' => $path,
                        'size' => $request->file('gambar')->getSize(),
                        'mime' => $request->file('gambar')->getMimeType()
                    ]);
                } else {
                    // Catat log kesalahan jika file tidak tersimpan
                    Log::error('Failed to verify updated file');
                    return redirect()->back()->with('error', 'Gagal menyimpan gambar.');
                }
            }

            // Perbarui data kategori
            $kategori->update($validated);

            // Redirect dengan pesan sukses
            return redirect()->route('pemilik-toko.kategori')->with('success', 'Kategori berhasil diupdate.');
        } catch (\Exception $e) {
            // Catat log kesalahan jika terjadi exception
            Log::error('Error updating toko kategori: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate kategori: ' . $e->getMessage());
        }
    }

    // Fungsi untuk menghapus kategori
    public function deleteKategori($id)
    {
        try {
            // Ambil data toko dari pengguna yang login
            $toko = auth()->user()->toko;
            // Ambil data kategori berdasarkan ID dan toko
            $kategori = TokoKategori::where('toko_id', $toko->id)->findOrFail($id);
            
            // Hapus gambar jika ada
            if ($kategori->gambar) {
                Storage::disk('public')->delete($kategori->gambar);
            }
            
            // Hapus kategori dari database
            $kategori->delete();

            // Redirect dengan pesan sukses
            return redirect()->route('pemilik-toko.kategori')->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            // Catat log kesalahan jika terjadi exception
            Log::error('Error deleting toko kategori: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    // Fungsi untuk mengelola pesanan pengiriman
    public function manageShippingOrders()
    {
        // Ambil data pengguna yang login
        $user = auth()->user();
        // Ambil data toko dari pengguna
        $toko = $user->toko;
        
        // Periksa apakah toko ada
        if (!$toko) {
            return redirect()->route('pemilik-toko.dashboard')
                ->with('error', 'Anda belum memiliki toko aktif.');
        }
        
        // Ambil pesanan pengiriman terkait toko ini
        $shippingOrders = ShippingOrder::with(['transaksi.user'])
            ->whereHas('transaksi.detailTransaksi', function($query) use ($toko) {
                // Filter item berdasarkan toko
                $query->where('item_type', 'toko_kategori')
                      ->where('nama_item', 'LIKE', '%' . $toko->nama . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Tampilkan view manajemen pengiriman
        return view('pemilik-toko.manage-shipping', compact('shippingOrders', 'toko'));
    }

    // Fungsi untuk memperbarui status pengiriman
    public function updateShippingStatus(Request $request, $id)
    {
        try {
            // Ambil data pengguna yang login
            $user = auth()->user();
            // Ambil data toko dari pengguna
            $toko = $user->toko;
            
            // Periksa apakah toko ada
            if (!$toko) {
                return redirect()->back()->with('error', 'Anda belum memiliki toko aktif.');
            }

            // Ambil data pengiriman terkait toko ini
            $shipping = ShippingOrder::whereHas('transaksi.detailTransaksi', function($query) use ($toko) {
                $query->where('item_type', 'toko_kategori')
                      ->where('nama_item', 'LIKE', '%' . $toko->nama . '%');
            })->findOrFail($id);
            
            // Validasi input status pengiriman
            $validated = $request->validate([
                'status' => 'required|in:pending,shipped,delivered,cancelled', // Status wajib diisi
                'courier' => 'nullable|string|max:100', // Kurir opsional
                'shipped_date' => 'nullable|date', // Tanggal pengiriman opsional
                'delivered_date' => 'nullable|date', // Tanggal pengiriman sampai opsional
                'notes' => 'nullable|string|max:1000', // Catatan opsional
            ]);

            // Perbarui data pengiriman
            $shipping->update([
                'status' => $validated['status'],
                'courier' => $validated['courier'] ?? $shipping->courier, // Gunakan nilai lama jika tidak ada input baru
                'shipped_date' => $validated['shipped_date'] ? \Carbon\Carbon::parse($validated['shipped_date']) : $shipping->shipped_date, // Parse tanggal jika ada
                'delivered_date' => $validated['delivered_date'] ? \Carbon\Carbon::parse($validated['delivered_date']) : $shipping->delivered_date, // Parse tanggal jika ada
                'notes' => $validated['notes'] ?? $shipping->notes, // Gunakan nilai lama jika tidak ada input baru
            ]);

            // Perbarui status transaksi berdasarkan status pengiriman
            if ($validated['status'] === 'delivered') {
                $shipping->transaksi->update(['status' => 'completed']);
            } elseif ($validated['status'] === 'cancelled') {
                $shipping->transaksi->update(['status' => 'cancelled']);
            } elseif ($validated['status'] === 'shipped') {
                $shipping->transaksi->update(['status' => 'processing']);
            }

            // Redirect dengan pesan sukses
            return redirect()->route('pemilik-toko.shipping')->with('success', 'Status pengiriman berhasil diupdate.');
            
        } catch (\Exception $e) {
            // Catat log kesalahan jika terjadi exception
            Log::error('Error updating shipping status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate status pengiriman: ' . $e->getMessage());
        }
    }
}