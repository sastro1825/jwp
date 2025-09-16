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

class CustomerController extends Controller
{
    /**
     * Buy produk dari kategori admin - masukkan ke keranjang
     */
    public function buyFromKategori($kategori_id)
    {
        try {
            DB::beginTransaction();

            $kategori = Kategori::findOrFail($kategori_id);
            $user_id = Auth::id();

            // Cek apakah item sudah ada di keranjang
            $existingItem = Keranjang::where('user_id', $user_id)
                ->where('kategori_id', $kategori_id)
                ->first();

            if ($existingItem) {
                // Update jumlah jika sudah ada
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

            DB::commit();
            return redirect()->route('customer.area')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error adding to cart: ' . $e->getMessage());
            return redirect()->route('customer.area')->with('error', 'Gagal menambahkan ke keranjang.');
        }
    }

    /**
     * Buy produk dari kategori toko - masukkan ke keranjang
     */
    public function buyFromTokoKategori($kategori_id)
    {
        try {
            DB::beginTransaction();

            $kategori = TokoKategori::with('toko')->findOrFail($kategori_id);
            $user_id = Auth::id();

            // Cek apakah item sudah ada di keranjang (berdasarkan nama karena ini dari toko)
            $existingItem = Keranjang::where('user_id', $user_id)
                ->where('nama_item', 'LIKE', '%' . $kategori->nama . '%')
                ->first();

            if ($existingItem) {
                // Update jumlah jika sudah ada
                $existingItem->increment('jumlah');
                $message = 'Jumlah ' . $kategori->nama . ' di keranjang berhasil ditambah.';
            } else {
                // Tambah item baru ke keranjang dari kategori toko
                Keranjang::create([
                    'user_id' => $user_id,
                    'kategori_id' => null, // Null karena ini bukan kategori admin
                    'nama_item' => $kategori->nama . ' (Toko: ' . $kategori->toko->nama . ')',
                    'harga_item' => $kategori->harga,
                    'jumlah' => 1,
                    'item_type' => 'kategori'
                ]);
                $message = $kategori->nama . ' dari ' . $kategori->toko->nama . ' berhasil ditambahkan ke keranjang.';
            }

            DB::commit();
            return redirect()->route('customer.area')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error adding toko kategori to cart: ' . $e->getMessage());
            return redirect()->route('customer.area')->with('error', 'Gagal menambahkan ke keranjang.');
        }
    }

    /**
     * Tampilkan keranjang belanja customer
     */
    public function keranjang()
    {
        $keranjangItems = Keranjang::where('user_id', Auth::id())
            ->with('kategori')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalHarga = $keranjangItems->sum(function($item) {
            return $item->jumlah * $item->harga_item;
        });

        return view('customer.keranjang', compact('keranjangItems', 'totalHarga'));
    }

    /**
     * Update jumlah item di keranjang
     */
    public function updateKeranjang(Request $request, $keranjang_id)
    {
        try {
            $validated = $request->validate([
                'jumlah' => 'required|integer|min:1|max:100',
            ]);

            $keranjangItem = Keranjang::where('id', $keranjang_id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $keranjangItem->update(['jumlah' => $validated['jumlah']]);
            return redirect()->back()->with('success', 'Jumlah item berhasil diupdate.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate item.');
        }
    }

    /**
     * Hapus item dari keranjang
     */
    public function hapusKeranjang($keranjang_id)
    {
        try {
            $keranjangItem = Keranjang::where('id', $keranjang_id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $keranjangItem->delete();
            return redirect()->back()->with('success', 'Item berhasil dihapus dari keranjang.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus item.');
        }
    }

    /**
     * Checkout - proses pemesanan dengan email notification dan buat shipping order otomatis
     */
    public function checkout(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'alamat_pengiriman' => 'required|string|max:500',
                'metode_pembayaran' => 'required|in:prepaid,postpaid',
                'catatan' => 'nullable|string|max:1000',
            ], [
                'alamat_pengiriman.required' => 'Alamat pengiriman wajib diisi.',
                'metode_pembayaran.required' => 'Metode pembayaran wajib dipilih.',
                'metode_pembayaran.in' => 'Metode pembayaran tidak valid.',
            ]);

            $user_id = Auth::id();
            $user = Auth::user();
            $keranjangItems = Keranjang::where('user_id', $user_id)->get();

            if ($keranjangItems->isEmpty()) {
                return redirect()->back()->with('error', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
            }

            // Hitung total dan pajak sesuai requirement SRS
            $subtotal = $keranjangItems->sum(function($item) {
                return $item->jumlah * $item->harga_item;
            });
            
            $pajak = $subtotal * 0.10; // Pajak 10% sesuai SRS requirement
            $total = $subtotal + $pajak;

            // Buat transaksi
            $transaksi = Transaksi::create([
                'user_id' => $user_id,
                'total' => $total, // Total sudah termasuk pajak
                'status' => 'pending',
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'alamat_pengiriman' => $validated['alamat_pengiriman'],
                'catatan' => $validated['catatan'],
            ]);

            // BUAT SHIPPING ORDER OTOMATIS
            $shippingOrder = ShippingOrder::create([
                'transaksi_id' => $transaksi->id,
                'tracking_number' => 'OSS-' . str_pad($transaksi->id, 6, '0', STR_PAD_LEFT) . '-' . date('Ymd'),
                'status' => 'pending',
                'courier' => $validated['metode_pembayaran'] === 'prepaid' ? 'Express Courier' : 'COD Service',
                'shipped_date' => null,
                'delivered_date' => null,
                'notes' => $validated['catatan'] ?: 'Pesanan dari ' . $user->name,
            ]);

            Log::info('Shipping order created automatically', [
                'transaksi_id' => $transaksi->id,
                'shipping_order_id' => $shippingOrder->id,
                'tracking_number' => $shippingOrder->tracking_number,
                'user_id' => $user_id
            ]);

            // Konversi keranjang items ke format untuk PDF laporan
            $items = $keranjangItems->map(function($item) {
                return (object) [
                    'nama' => $item->nama_item,
                    'jumlah' => $item->jumlah,
                    'harga' => $item->harga_item,
                    'item_type' => 'kategori',
                    'kategori_id' => $item->kategori_id,
                    'produk' => null,
                ];
            });

            // Generate PDF menggunakan view yang sudah ada
            $pdfPath = null;
            try {
                $pdf = Pdf::loadView('laporan-pembelian', [
                    'user' => $user,
                    'transaksi' => $transaksi,
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'pajak' => $pajak,
                    'total' => $total,
                ]);

                // Simpan PDF ke storage temp
                $fileName = 'laporan-pembelian-' . $transaksi->id . '.pdf';
                $pdfPath = storage_path('app/temp/' . $fileName);
                
                // Buat direktori temp jika belum ada
                if (!file_exists(storage_path('app/temp'))) {
                    mkdir(storage_path('app/temp'), 0755, true);
                }
                
                $pdf->save($pdfPath);
                
                Log::info('PDF generated successfully', [
                    'transaksi_id' => $transaksi->id,
                    'pdf_path' => $pdfPath
                ]);
            } catch (\Exception $pdfError) {
                Log::error('Failed to generate PDF', [
                    'transaksi_id' => $transaksi->id,
                    'error' => $pdfError->getMessage()
                ]);
            }

            // Kosongkan keranjang setelah checkout berhasil
            Keranjang::where('user_id', $user_id)->delete();

            // KIRIM EMAIL MENGGUNAKAN LaporanMail yang sudah ada
            try {
                Mail::to($user->email)->send(new LaporanMail($pdfPath, $transaksi));
                
                Log::info('Checkout email sent successfully using LaporanMail', [
                    'user_id' => $user_id,
                    'transaksi_id' => $transaksi->id,
                    'email' => $user->email,
                    'pdf_attached' => $pdfPath ? 'yes' : 'no',
                    'tracking_number' => $shippingOrder->tracking_number
                ]);
                
                $emailStatus = 'Email laporan pembelian telah dikirim ke ' . $user->email;
                
                // Hapus file PDF setelah email terkirim
                if ($pdfPath && file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
                
            } catch (\Exception $emailError) {
                Log::error('Failed to send checkout email', [
                    'user_id' => $user_id,
                    'transaksi_id' => $transaksi->id,
                    'error' => $emailError->getMessage()
                ]);
                $emailStatus = 'Email laporan gagal dikirim, namun pesanan telah diproses.';
                
                // Hapus file PDF jika email gagal
                if ($pdfPath && file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
            }

            DB::commit();

            // Redirect berdasarkan role dengan pesan sukses
            $successMessage = 'Pesanan #' . $transaksi->id . ' berhasil dibuat (Total: Rp ' . number_format($total, 0, ',', '.') . '). ' . 
                             'Tracking: ' . $shippingOrder->tracking_number . '. ' . $emailStatus;

            if (auth()->user()->role === 'pemilik_toko') {
                return redirect()->route('pemilik-toko.order.history')->with('success', $successMessage);
            } else {
                return redirect()->route('customer.order.history')->with('success', $successMessage);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Checkout error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Gagal memproses pesanan. Silakan coba lagi.');
        }
    }

    /**
     * Riwayat pesanan customer
     */
    public function orderHistory()
    {
        $transaksis = Transaksi::where('user_id', Auth::id())
            ->with('shippingOrder') // Load shipping order untuk tracking
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.order-history', compact('transaksis'));
    }

    /**
     * Cancel pesanan
     */
    public function cancelOrder($id)
    {
        try {
            $transaksi = Transaksi::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();

            // Update status transaksi dan shipping order
            $transaksi->update(['status' => 'cancelled']);
            
            // Update shipping order jika ada
            if ($transaksi->shippingOrder) {
                $transaksi->shippingOrder->update(['status' => 'cancelled']);
            }

            return redirect()->back()->with('success', 'Pesanan #' . $id . ' berhasil dibatalkan.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan pesanan atau pesanan tidak ditemukan.');
        }
    }

    /**
     * Download laporan transaksi
     */
    public function downloadLaporan($transaksi_id)
    {
        try {
            $transaksi = Transaksi::where('id', $transaksi_id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $user = Auth::user();
            
            // Buat items dummy berdasarkan transaksi untuk PDF
            $items = collect([
                (object) [
                    'nama' => 'Produk dari Transaksi #' . $transaksi->id,
                    'jumlah' => 1,
                    'harga' => $transaksi->total / 1.1, // Sebelum pajak
                    'item_type' => 'kategori',
                    'kategori_id' => 1,
                    'produk' => null,
                ]
            ]);

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
            Log::error('Download laporan error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mendownload laporan.');
        }
    }

    /**
     * Submit feedback dari customer yang sudah login
     */
    public function submitFeedback(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:1000|min:10',
            ], [
                'message.required' => 'Pesan feedback wajib diisi.',
                'message.min' => 'Pesan minimal 10 karakter.',
                'message.max' => 'Pesan maksimal 1000 karakter.',
            ]);

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
            Log::error('Error submitting customer feedback: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengirim feedback. Silakan coba lagi.');
        }
    }

    /**
     * Tampilkan form permohonan toko - DIPERBAIKI
     */
    public function showTokoRequestForm()
    {
        // Cek apakah user sudah punya permohonan pending atau approved
        $existingRequest = TokoRequest::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        $approvedRequest = TokoRequest::where('user_id', auth()->id())
            ->where('status', 'approved')
            ->first();

        // Pass variabel ke view
        return view('customer.toko-request-form', compact('existingRequest', 'approvedRequest'));
    }

    /**
     * Submit permohonan toko
     */
    public function submitTokoRequest(Request $request)
    {
        try {
            $existingRequest = TokoRequest::where('user_id', auth()->id())
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existingRequest) {
                return redirect()->route('customer.toko.status')->with('error', 
                    'Anda sudah memiliki permohonan toko yang ' . $existingRequest->status);
            }

            $validated = $request->validate([
                'nama_toko' => 'required|string|max:100',
                'deskripsi_toko' => 'nullable|string|max:1000',
                'kategori_usaha' => 'required|in:alat-kesehatan,obat-obatan,suplemen-kesehatan,perawatan-kecantikan,kesehatan-pribadi',
                'alamat_toko' => 'required|string|max:500',
                'no_telepon' => 'nullable|string|max:20',
                'alasan_permohonan' => 'required|string|max:1000',
            ], [
                'nama_toko.required' => 'Nama toko wajib diisi.',
                'kategori_usaha.required' => 'Kategori usaha wajib dipilih.',
                'alamat_toko.required' => 'Alamat toko wajib diisi.',
                'alasan_permohonan.required' => 'Alasan permohonan wajib diisi.',
            ]);

            $validated['user_id'] = auth()->id();
            $validated['status'] = 'pending';

            TokoRequest::create($validated);
            return redirect()->route('customer.toko.status')->with('success', 
                'Permohonan toko berhasil dikirim. Silakan tunggu review dari admin.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error submitting toko request: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengirim permohonan toko. Silakan coba lagi.');
        }
    }

    /**
     * Lihat status permohonan toko
     */
    public function viewTokoStatus()
    {
        $tokoRequests = TokoRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.toko-status', compact('tokoRequests'));
    }

    /**
     * Get keranjang data untuk AJAX
     */
    public function getKeranjangData()
    {
        try {
            $keranjangItems = Keranjang::where('user_id', Auth::id())->get();
            $totalItems = $keranjangItems->sum('jumlah');
            $totalHarga = $keranjangItems->sum(function($item) {
                return $item->jumlah * $item->harga_item;
            });

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