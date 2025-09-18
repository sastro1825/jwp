<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\Transaksi;
use App\Models\GuestBook;
use App\Models\TokoRequest;
use App\Models\ShippingOrder;
use App\Models\TokoKategori;
use App\Mail\LaporanMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

// Kelas untuk mengelola fungsi-fungsi pelanggan
class CustomerController extends Controller
{
    // Memuat dan memvalidasi gambar kategori toko
    private function getValidatedKategoriToko()
    {
        // Ambil data kategori toko dengan relasi toko yang disetujui
        return TokoKategori::with('toko')
            ->whereHas('toko', function($query) {
                // Hanya toko dengan status approved
                $query->where('status', 'approved');
            })
            ->get()
            ->map(function($kategori) {
                // Validasi keberadaan dan ukuran file gambar
                if ($kategori->gambar) {
                    $fullPath = storage_path('app/public/' . $kategori->gambar);
                    $kategori->image_valid = file_exists($fullPath) && filesize($fullPath) > 0;
                    $kategori->image_url = asset('storage/' . $kategori->gambar);
                } else {
                    $kategori->image_valid = false;
                    $kategori->image_url = null;
                }
                return $kategori;
            });
    }

    // Memeriksa ketersediaan COD berdasarkan kota
    private function canUseCOD($keranjangItems, $userCity)
    {
        // Jika kota pengguna tidak ada, COD tidak tersedia
        if (!$userCity) {
            Log::info('COD Check: User tidak punya kota', ['user_city' => $userCity]);
            return false;
        }

        // Ambil item dari toko kategori
        $tokoItems = $keranjangItems->where('item_type', 'toko_kategori');
        
        // Jika tidak ada item toko, bandingkan kota admin
        if ($tokoItems->isEmpty()) {
            $adminCity = \App\Models\User::where('role', 'admin')->first()->city ?? null;
            
            // Jika kota admin tidak ada, COD tidak tersedia
            if (!$adminCity) {
                Log::info('COD Check: Admin tidak punya kota', ['admin_city' => $adminCity]);
                return false;
            }
            
            $userCityLower = strtolower(trim($userCity));
            $adminCityLower = strtolower(trim($adminCity));
            
            // Log perbandingan kota pengguna dan admin
            Log::info('COD Check: Customer vs Admin kota', [
                'user_city' => $userCityLower,
                'admin_city' => $adminCityLower,
                'same_city' => $userCityLower === $adminCityLower
            ]);
            
            return $userCityLower === $adminCityLower;
        }

        // Cek kota setiap toko untuk item toko
        foreach ($tokoItems as $item) {
            // Ekstrak nama toko dari nama_item
            preg_match('/\(Toko: (.+)\)/', $item->nama_item, $matches);
            if (isset($matches[1])) {
                $namaTokoFromItem = $matches[1];
                
                // Cari toko berdasarkan nama
                $toko = \App\Models\Toko::where('nama', $namaTokoFromItem)->first();
                if ($toko && $toko->user) {
                    $tokoCity = strtolower(trim($toko->user->city));
                    $customerCity = strtolower(trim($userCity));
                    
                    // Log perbandingan kota pengguna dan toko
                    Log::info('COD Check: Customer vs Toko kota', [
                        'user_city' => $customerCity,
                        'toko_city' => $tokoCity,
                        'toko_name' => $namaTokoFromItem,
                        'same_city' => $tokoCity === $customerCity
                    ]);
                    
                    // Jika kota toko berbeda, COD tidak tersedia
                    if ($tokoCity !== $customerCity) {
                        return false;
                    }
                }
            }
        }
        
        return true; // Semua toko berada di kota yang sama
    }

    // Menambahkan produk kategori admin ke keranjang
    public function buyFromKategori($kategori_id)
    {
        try {
            // Mulai transaksi database
            DB::beginTransaction();

            // Cari kategori berdasarkan ID
            $kategori = Kategori::findOrFail($kategori_id);
            $user_id = Auth::id();

            // Cek apakah item sudah ada di keranjang
            $existingItem = Keranjang::where('user_id', $user_id)
                ->where('kategori_id', $kategori_id)
                ->first();

            if ($existingItem) {
                // Tambah jumlah jika item sudah ada
                $existingItem->increment('jumlah');
                $message = 'Jumlah ' . $kategori->nama . ' di keranjang berhasil ditambah.';
            } else {
                // Tambah item baru ke keranjang
                Keranjang::create([
                    'user_id' => $user_id,
                    'kategori_id' => $kategori_id,
                    'nama_item' => $kategori->nama,
                    'harga_item' => $kategori->harga,
                    'jumlah' => 1,
                    'item_type' => 'kategori'
                ]);
                $message = $kategori->nama . ' berhasil ditambahkan ke keranjang.';
            }

            // Commit transaksi
            DB::commit();
            return redirect()->route('customer.area')->with('success', $message);

        } catch (\Exception $e) {
            // Rollback jika terjadi error
            DB::rollback();
            Log::error('Error menambah ke keranjang: ' . $e->getMessage());
            return redirect()->route('customer.area')->with('error', 'Gagal menambahkan ke keranjang.');
        }
    }

    // Menambahkan produk kategori toko ke keranjang
    public function buyFromTokoKategori($kategori_id)
    {
        try {
            // Mulai transaksi database
            DB::beginTransaction();

            // Ambil kategori toko dengan relasi toko
            $kategori = TokoKategori::with('toko')->findOrFail($kategori_id);
            $user_id = Auth::id();

            // Cek apakah item sudah ada di keranjang
            $existingItem = Keranjang::where('user_id', $user_id)
                ->where('nama_item', 'LIKE', '%' . $kategori->nama . '%')
                ->where('item_type', 'toko_kategori')
                ->first();

            if ($existingItem) {
                // Tambah jumlah jika item sudah ada
                $existingItem->increment('jumlah');
                $message = 'Jumlah ' . $kategori->nama . ' di keranjang berhasil ditambah.';
            } else {
                // Tambah item baru ke keranjang
                Keranjang::create([
                    'user_id' => $user_id,
                    'kategori_id' => null,
                    'nama_item' => $kategori->nama . ' (Toko: ' . $kategori->toko->nama . ')',
                    'harga_item' => $kategori->harga,
                    'jumlah' => 1,
                    'item_type' => 'toko_kategori'
                ]);
                $message = $kategori->nama . ' dari ' . $kategori->toko->nama . ' berhasil ditambahkan ke keranjang.';
            }

            // Commit transaksi
            DB::commit();
            return redirect()->route('customer.area')->with('success', $message);

        } catch (\Exception $e) {
            // Rollback jika terjadi error
            DB::rollback();
            Log::error('Error menambah kategori toko ke keranjang: ' . $e->getMessage());
            return redirect()->route('customer.area')->with('error', 'Gagal menambahkan ke keranjang.');
        }
    }

    // Menampilkan keranjang belanja pelanggan
    public function keranjang()
    {
        // Ambil item keranjang pengguna
        $keranjangItems = Keranjang::where('user_id', Auth::id())
            ->with('kategori')
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung total harga
        $totalHarga = $keranjangItems->sum(function($item) {
            return $item->jumlah * $item->harga_item;
        });

        // Cek ketersediaan COD
        $canUseCOD = $this->canUseCOD($keranjangItems, Auth::user()->city);

        // Tampilkan view keranjang
        return view('customer.keranjang', compact('keranjangItems', 'totalHarga', 'canUseCOD'));
    }

    // Memperbarui jumlah item di keranjang
    public function updateKeranjang(Request $request, $keranjang_id)
    {
        try {
            // Validasi input jumlah
            $validated = $request->validate([
                'jumlah' => 'required|integer|min:1|max:100',
            ]);

            // Cari item keranjang
            $keranjangItem = Keranjang::where('id', $keranjang_id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Update jumlah item
            $keranjangItem->update(['jumlah' => $validated['jumlah']]);
            return redirect()->back()->with('success', 'Jumlah item berhasil diupdate.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate item.');
        }
    }

    // Menghapus item dari keranjang
    public function hapusKeranjang($keranjang_id)
    {
        try {
            // Cari item keranjang
            $keranjangItem = Keranjang::where('id', $keranjang_id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Hapus item
            $keranjangItem->delete();
            return redirect()->back()->with('success', 'Item berhasil dihapus dari keranjang.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus item.');
        }
    }

    // Proses checkout pesanan
    public function checkout(Request $request)
    {
        try {
            // Mulai transaksi database
            DB::beginTransaction();

            // Validasi input checkout
            $validated = $request->validate([
                'alamat_pengiriman' => 'required|string|max:500',
                'metode_pembayaran' => 'required|in:prepaid,postpaid',
                'catatan' => 'nullable|string|max:1000',
            ]);

            $user_id = Auth::id();
            $user = Auth::user();
            $keranjangItems = Keranjang::where('user_id', $user_id)->get();

            // Cek apakah keranjang kosong
            if ($keranjangItems->isEmpty()) {
                return redirect()->back()->with('error', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
            }

            // Validasi COD sebelum checkout
            if ($validated['metode_pembayaran'] === 'postpaid') {
                $canUseCOD = $this->canUseCOD($keranjangItems, $user->city);
                
                if (!$canUseCOD) {
                    Log::warning('COD Blocked at Checkout', [
                        'user_id' => $user_id,
                        'user_city' => $user->city,
                        'payment_method' => $validated['metode_pembayaran']
                    ]);
                    
                    return redirect()->back()->with('error', 
                        'COD tidak tersedia karena Anda tidak se-kota dengan admin/toko. Silakan gunakan prepaid.');
                }
            }

            // Hitung total harga
            $total = $keranjangItems->sum(function($item) {
                return (float) $item->jumlah * (float) $item->harga_item;
            });

            // Buat transaksi baru
            $transaksi = Transaksi::create([
                'user_id' => $user_id,
                'total' => $total,
                'status' => 'pending',
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'alamat_pengiriman' => $validated['alamat_pengiriman'],
                'catatan' => $validated['catatan'],
            ]);

            // Simpan detail transaksi
            foreach ($keranjangItems as $item) {
                \App\Models\DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'nama_item' => $item->nama_item ?? 'Item tidak diketahui',
                    'harga_item' => (float) ($item->harga_item ?? 0),
                    'jumlah' => (int) ($item->jumlah ?? 1),
                    'subtotal_item' => (float) (($item->jumlah ?? 1) * ($item->harga_item ?? 0)),
                    'item_type' => $item->item_type ?? 'kategori',
                    'deskripsi_item' => $item->deskripsi_item ?? null
                ]);
            }

            // Buat shipping order
            $shippingOrder = ShippingOrder::create([
                'transaksi_id' => $transaksi->id,
                'tracking_number' => 'OSS-' . str_pad($transaksi->id, 6, '0', STR_PAD_LEFT) . '-' . date('Ymd'),
                'status' => 'pending',
                'courier' => $validated['metode_pembayaran'] === 'prepaid' ? 'Express Courier' : 'COD Service',
                'shipped_date' => null,
                'delivered_date' => null,
                'notes' => $validated['catatan'] ?: 'Pesanan dari ' . $user->name,
            ]);

            // Generate PDF laporan
            $items = \App\Models\DetailTransaksi::where('transaksi_id', $transaksi->id)->get();
            $pdfPath = null;
            try {
                $pdf = Pdf::loadView('laporan-pembelian', [
                    'user' => $user,
                    'transaksi' => $transaksi,
                    'items' => $items,
                    'total' => $total,
                ]);

                $fileName = 'laporan-pembelian-' . $transaksi->id . '.pdf';
                $pdfPath = storage_path('app/temp/' . $fileName);
                
                // Buat direktori temp jika belum ada
                if (!file_exists(storage_path('app/temp'))) {
                    mkdir(storage_path('app/temp'), 0755, true);
                }
                
                $pdf->save($pdfPath);
            } catch (\Exception $pdfError) {
                Log::error('Gagal generate PDF: ' . $pdfError->getMessage());
            }

            // Kirim email laporan
            try {
                Mail::to($user->email)->send(new LaporanMail($pdfPath, $transaksi));
                $emailStatus = 'Email laporan pembelian telah dikirim ke ' . $user->email;
                
                // Hapus file PDF setelah pengiriman
                if ($pdfPath && file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
            } catch (\Exception $emailError) {
                Log::error('Gagal kirim email: ' . $emailError->getMessage());
                $emailStatus = 'Email laporan gagal dikirim, namun pesanan telah diproses.';
                
                // Hapus file PDF jika gagal
                if ($pdfPath && file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
            }

            // Kosongkan keranjang
            Keranjang::where('user_id', $user_id)->delete();

            // Commit transaksi
            DB::commit();

            // Pesan sukses
            $successMessage = 'Pesanan #' . $transaksi->id . ' berhasil dibuat (Total: Rp ' . number_format($total, 0, ',', '.') . '). ' . 
                             'Tracking: ' . $shippingOrder->tracking_number . '. ' . $emailStatus;

            // Arahkan ke rute sesuai peran pengguna
            if (auth()->user()->role === 'pemilik_toko') {
                return redirect()->route('pemilik-toko.order.history')->with('success', $successMessage);
            } else {
                return redirect()->route('customer.order.history')->with('success', $successMessage);
            }

        } catch (\Exception $e) {
            // Rollback jika terjadi error
            DB::rollback();
            Log::error('Error checkout: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses pesanan. Silakan coba lagi.');
        }
    }

    // Menampilkan riwayat pesanan pelanggan
    public function orderHistory()
    {
        // Ambil transaksi pengguna dengan pagination
        $transaksis = Transaksi::where('user_id', Auth::id())
            ->with('shippingOrder')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Tampilkan view riwayat pesanan
        return view('customer.order-history', compact('transaksis'));
    }

    // Membatalkan pesanan
    public function cancelOrder($id)
    {
        try {
            // Cari transaksi yang masih pending
            $transaksi = Transaksi::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();

            // Update status transaksi ke cancelled
            $transaksi->update(['status' => 'cancelled']);
            
            // Update status shipping order jika ada
            if ($transaksi->shippingOrder) {
                $transaksi->shippingOrder->update(['status' => 'cancelled']);
            }

            return redirect()->back()->with('success', 'Pesanan #' . $id . ' berhasil dibatalkan.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan pesanan atau pesanan tidak ditemukan.');
        }
    }

    // Mengunduh laporan transaksi
    public function downloadLaporan($transaksi_id)
    {
        try {
            // Cari transaksi pengguna
            $transaksi = Transaksi::where('id', $transaksi_id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $user = Auth::user();
            
            // Buat data dummy untuk PDF
            $items = collect([
                (object) [
                    'nama' => 'Produk dari Transaksi #' . $transaksi->id,
                    'jumlah' => 1,
                    'harga' => $transaksi->total / 1.1,
                    'item_type' => 'kategori',
                    'kategori_id' => 1,
                    'produk' => null,
                ]
            ]);

            // Hitung subtotal dan pajak
            $subtotal = $transaksi->total / 1.1;
            $pajak = $subtotal * 0.1;
            $total = $transaksi->total;

            // Generate PDF
            $pdf = Pdf::loadView('laporan-pembelian', [
                'user' => $user,
                'transaksi' => $transaksi,
                'items' => $items,
                'subtotal' => $subtotal,
                'pajak' => $pajak,
                'total' => $total,
            ]);

            $fileName = 'laporan-pembelian-' . $transaksi->id . '.pdf';
            return $pdf->download($fileName);

        } catch (\Exception $e) {
            Log::error('Error download laporan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mendownload laporan.');
        }
    }

    // Mengirim feedback pelanggan
    public function submitFeedback(Request $request)
    {
        try {
            // Validasi input feedback
            $validated = $request->validate([
                'message' => 'required|string|max:1000|min:10',
            ], [
                'message.required' => 'Pesan feedback wajib diisi.',
                'message.min' => 'Pesan minimal 10 karakter.',
                'message.max' => 'Pesan maksimal 1000 karakter.',
            ]);

            // Simpan feedback
            GuestBook::create([
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'message' => $validated['message'],
                'status' => 'pending',
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Terima kasih! Feedback Anda telah dikirim dan akan dimoderasi oleh admin.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error mengirim feedback: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengirim feedback. Silakan coba lagi.');
        }
    }

    // Menampilkan form permohonan toko
    public function showTokoRequestForm()
    {
        // Cek permohonan toko yang masih pending
        $existingRequest = TokoRequest::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        // Cek permohonan toko yang sudah disetujui
        $approvedRequest = TokoRequest::where('user_id', auth()->id())
            ->where('status', 'approved')
            ->first();

        // Ambil data pengguna
        $user = auth()->user();

        // Tampilkan form permohonan toko
        return view('customer.toko-request-form', compact('existingRequest', 'approvedRequest', 'user'));
    }

    // Mengirim permohonan toko
    public function submitTokoRequest(Request $request)
    {
        try {
            // Cek apakah ada permohonan yang masih pending atau disetujui
            $existingRequest = TokoRequest::where('user_id', auth()->id())
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existingRequest) {
                return redirect()->route('customer.toko.status')->with('error', 
                    'Anda sudah memiliki permohonan toko yang ' . $existingRequest->status);
            }

            // Validasi input permohonan
            $validated = $request->validate([
                'nama_toko' => 'required|string|max:100',
                'deskripsi_toko' => 'nullable|string|max:1000',
                'kategori_usaha' => 'required|in:alat-kesehatan,obat-obatan,suplemen-kesehatan,perawatan-kecantikan,kesehatan-pribadi',
                'alamat_toko' => 'required|string|max:500',
                'no_telepon' => 'nullable|string|max:20',
                'alasan_permohonan' => 'required|string|max:1000',
            ]);

            // Tambahkan user_id dan status
            $validated['user_id'] = auth()->id();
            $validated['status'] = 'pending';

            // Simpan permohonan
            TokoRequest::create($validated);
            return redirect()->route('customer.toko.status')->with('success', 
                'Permohonan toko berhasil dikirim. Silakan tunggu review dari admin.');

        } catch (\Exception $e) {
            Log::error('Error mengirim permohonan toko: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengirim permohonan toko. Silakan coba lagi.');
        }
    }

    // Menampilkan status permohonan toko
    public function viewTokoStatus()
    {
        // Ambil daftar permohonan toko pengguna
        $tokoRequests = TokoRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Tampilkan view status permohonan
        return view('customer.toko-status', compact('tokoRequests'));
    }

    // Mengambil data keranjang untuk AJAX
    public function getKeranjangData()
    {
        try {
            // Ambil item keranjang pengguna
            $keranjangItems = Keranjang::where('user_id', Auth::id())->get();
            $totalItems = $keranjangItems->sum('jumlah');
            $totalHarga = $keranjangItems->sum(function($item) {
                return $item->jumlah * $item->harga_item;
            });

            // Kembalikan data dalam format JSON
            return response()->json([
                'success' => true,
                'totalItems' => $totalItems,
                'totalHarga' => $totalHarga,
                'items' => $keranjangItems->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data keranjang'
            ]);
        }
    }
}