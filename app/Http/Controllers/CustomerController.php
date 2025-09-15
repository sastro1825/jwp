<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Keranjang;
use App\Models\Transaksi;
use App\Models\Kategori;
use App\Models\GuestBook;
use App\Models\ShippingOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\LaporanMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Buy langsung dari kategori - menggunakan model Keranjang yang sudah ada
     * Langsung buat item keranjang berdasarkan data kategori
     */
    public function buyFromKategori($kategori_id)
    {
        // Cari kategori berdasarkan ID
        $kategori = Kategori::find($kategori_id);
        
        if (!$kategori) {
            return redirect()->back()->with('error', 'Kategori tidak ditemukan.');
        }

        // Cek apakah kategori ini sudah ada di keranjang user
        $keranjangExisting = Keranjang::where('user_id', auth()->id())
                                    ->where('kategori_id', $kategori->id)
                                    ->where('item_type', 'kategori')
                                    ->first();
        
        if ($keranjangExisting) {
            // Jika sudah ada, tambah 1
            $newJumlah = $keranjangExisting->jumlah + 1;
            if ($newJumlah > 10) $newJumlah = 10; // Batasi maksimal 10
            
            $keranjangExisting->update(['jumlah' => $newJumlah]);
            
            return redirect()->route('customer.keranjang')->with('success', "Kategori {$kategori->nama} berhasil ditambahkan ke keranjang. Jumlah: {$newJumlah}");
        } else {
            // Jika belum ada, buat item baru menggunakan helper method
            Keranjang::createFromKategori(auth()->id(), $kategori, 1);
            
            return redirect()->route('customer.keranjang')->with('success', "Kategori {$kategori->nama} berhasil ditambahkan ke keranjang.");
        }
    }

    /**
     * Tampilkan keranjang belanja - untuk kategori saja
     * Menampilkan semua item kategori di keranjang dengan total harga
     */
    public function keranjang()
    {
        // Ambil semua item kategori di keranjang user yang login
        $items = Keranjang::where('user_id', auth()->id())
                         ->where('item_type', 'kategori')
                         ->with(['kategori'])
                         ->get();
        
        // Hitung total harga sebelum pajak (subtotal)
        $subtotal = $items->sum(function($item) {
            return $item->jumlah * $item->harga;
        });
        
        // Hitung pajak 10% sesuai SRS requirement
        $pajak = $subtotal * 0.1;
        $total = $subtotal + $pajak;
        
        return view('keranjang-belanja', compact('items', 'subtotal', 'pajak', 'total'));
    }

    /**
     * Proses checkout pembelian - dengan auto-create shipping order
     * Membuat transaksi, generate PDF, dan buat shipping order otomatis untuk admin
     */
    public function checkout(Request $request)
    {
        try {
            // Log awal proses checkout untuk debugging
            Log::info('Memulai proses checkout untuk user: ' . auth()->id());

            // Validasi metode pembayaran sesuai SRS (prepaid/postpaid)
            $request->validate([
                'metode_pembayaran' => 'required|in:prepaid,postpaid'
            ]);

            // Ambil semua item kategori di keranjang
            $items = Keranjang::where('user_id', auth()->id())
                             ->where('item_type', 'kategori')
                             ->with(['kategori'])
                             ->get();
            
            // Cek apakah keranjang kosong
            if ($items->isEmpty()) {
                Log::warning('Keranjang kosong untuk user: ' . auth()->id());
                return redirect()->route('customer.keranjang')->with('error', 'Keranjang kosong. Tambahkan kategori terlebih dahulu.');
            }

            // Hitung total dengan pajak 10% sesuai SRS
            $subtotal = $items->sum(function($item) {
                return $item->jumlah * $item->harga;
            });
            $pajak = $subtotal * 0.1; // 10% pajak
            $total = $subtotal + $pajak;

            Log::info('Menghitung total belanja', ['subtotal' => $subtotal, 'pajak' => $pajak, 'total' => $total]);

            // Buat transaksi baru di database
            $transaksi = Transaksi::create([
                'user_id' => auth()->id(),
                'total' => $total,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status' => 'completed'
            ]);

            Log::info('Transaksi berhasil dibuat', ['transaksi_id' => $transaksi->id]);

            // AUTO-CREATE SHIPPING ORDER agar otomatis masuk ke admin shipping
            try {
                // Generate tracking number dengan format OSS-YYYYMMDD-000001
                $trackingNumber = 'OSS-' . date('Ymd') . '-' . str_pad($transaksi->id, 6, '0', STR_PAD_LEFT);
                
                ShippingOrder::create([
                    'transaksi_id' => $transaksi->id,
                    'tracking_number' => $trackingNumber,
                    'status' => 'pending', // Status pending untuk admin proses
                    'courier' => null, // Admin akan pilih kurir nanti
                    'notes' => 'Pesanan baru - menunggu konfirmasi admin untuk pengiriman'
                ]);
                
                Log::info('Shipping order otomatis dibuat', ['tracking' => $trackingNumber]);
            } catch (\Exception $e) {
                Log::error('Gagal membuat shipping order: ' . $e->getMessage());
                // Lanjutkan proses meskipun shipping order gagal
            }

            // Siapkan data untuk PDF laporan menggunakan view yang sudah ada
            $user = auth()->user();
            $dataLaporan = [
                'transaksi' => $transaksi,
                'user' => $user,
                'items' => $items, // Items dari kategori
                'subtotal' => $subtotal,
                'pajak' => $pajak,
                'total' => $total
            ];

            // Generate PDF dan kirim email
            $pdfPath = null;
            $fullPdfPath = null;

            try {
                // Pastikan direktori laporan ada
                $laporanDir = storage_path('app/public/laporan');
                if (!File::exists($laporanDir)) {
                    File::makeDirectory($laporanDir, 0755, true);
                    Log::info('Directory laporan dibuat: ' . $laporanDir);
                }

                // Generate PDF laporan menggunakan view yang sudah ada: laporan-pembelian.blade.php
                $pdf = Pdf::loadView('laporan-pembelian', $dataLaporan);
                $pdfFileName = 'transaksi-' . $transaksi->id . '.pdf';
                $pdfPath = 'laporan/' . $pdfFileName;
                $fullPdfPath = storage_path('app/public/' . $pdfPath);

                // Simpan PDF ke storage
                $pdf->save($fullPdfPath);
                Log::info('PDF berhasil disimpan: ' . $fullPdfPath);
                
                // Update transaksi dengan path PDF
                $transaksi->update(['pdf_path' => $pdfPath]);

            } catch (\Exception $e) {
                Log::error('PDF generation failed: ' . $e->getMessage());
                Log::error('PDF error details: ' . $e->getTraceAsString());
                // Lanjutkan proses meskipun PDF gagal
            }

            // Kirim laporan ke email customer menggunakan view yang sudah ada
            try {
                if ($fullPdfPath && File::exists($fullPdfPath)) {
                    // Gunakan LaporanMail yang sudah ada dengan view emails/laporan.blade.php
                    Mail::to($user->email)->send(new LaporanMail($fullPdfPath, $transaksi));
                    Log::info('Email berhasil dikirim ke: ' . $user->email);
                } else {
                    Log::warning('PDF tidak ditemukan, email tetap dikirim tanpa attachment');
                    // Kirim email tanpa attachment jika PDF gagal
                    Mail::to($user->email)->send(new LaporanMail(null, $transaksi));
                }
            } catch (\Exception $e) {
                // Log error email tapi lanjutkan proses
                Log::error('Email laporan gagal dikirim: ' . $e->getMessage());
                Log::error('Email error details: ' . $e->getTraceAsString());
            }

            // Kosongkan keranjang setelah checkout berhasil
            Keranjang::where('user_id', auth()->id())->delete();
            Log::info('Keranjang dibersihkan untuk user: ' . auth()->id());

            $successMessage = 'Pembelian berhasil! ID Transaksi: ' . $transaksi->id . '. Pesanan sedang diproses admin untuk pengiriman.';
            if ($fullPdfPath && File::exists($fullPdfPath)) {
                $successMessage .= ' Laporan telah dikirim ke email Anda.';
            }

            return redirect()->route('home')->with('success', $successMessage);

        } catch (\Exception $e) {
            // Log error umum untuk debugging
            Log::error('Checkout process failed: ' . $e->getMessage());
            Log::error('Checkout error details: ' . $e->getTraceAsString());
            
            return redirect()->route('customer.keranjang')->with('error', 'Terjadi kesalahan saat memproses transaksi. Silakan coba lagi. Detail: ' . $e->getMessage());
        }
    }

    /**
     * Update atau hapus item dari keranjang - untuk kategori
     * Mengubah jumlah item di keranjang
     */
    public function updateKeranjang(Request $request, $keranjang_id)
    {
        // Validasi input quantity
        $request->validate([
            'jumlah' => 'required|integer|min:1|max:10'
        ]);

        // Cari item keranjang milik user yang login
        $keranjang = Keranjang::where('id', $keranjang_id)
                             ->where('user_id', auth()->id())
                             ->where('item_type', 'kategori')
                             ->firstOrFail();

        // Update quantity
        $keranjang->update(['jumlah' => $request->jumlah]);

        return redirect()->route('customer.keranjang')->with('success', 'Jumlah item berhasil diperbarui.');
    }

    /**
     * Hapus item dari keranjang - untuk kategori
     * Menghapus item dari keranjang belanja
     */
    public function hapusKeranjang($keranjang_id)
    {
        // Cari dan hapus item keranjang milik user yang login
        $keranjang = Keranjang::where('id', $keranjang_id)
                             ->where('user_id', auth()->id())
                             ->where('item_type', 'kategori')
                             ->firstOrFail();

        $itemNama = $keranjang->nama;
        $keranjang->delete();

        return redirect()->route('customer.keranjang')->with('success', "Item {$itemNama} berhasil dihapus dari keranjang.");
    }

    /**
     * Submit feedback ke guest book - customer give feedback
     * Customer dapat memberikan feedback tentang pengalaman berbelanja
     */
    public function submitFeedback(Request $request)
    {
        // Validasi input feedback
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        // Simpan feedback ke guest book dengan status pending (perlu approve admin)
        GuestBook::create([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'message' => $request->message,
            'status' => 'pending' // Admin perlu approve dulu sebelum ditampilkan
        ]);

        return redirect()->back()->with('success', 'Feedback berhasil dikirim. Menunggu moderasi admin.');
    }

    /**
     * View order history - customer lihat riwayat pesanan (GANTI MY ACCOUNT)
     * Menampilkan riwayat transaksi dengan status pengiriman dan tombol cancel
     */
    public function orderHistory()
    {
        $user = auth()->user();
        
        // Ambil riwayat transaksi customer dengan pagination dan relasi shipping
        $transaksis = Transaksi::where('user_id', $user->id)
                              ->with(['shippingOrder'])
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);

        return view('customer.order-history', compact('user', 'transaksis'));
    }

    /**
     * Cancel order sebelum dikirim - customer cancel order before shipping
     * Customer bisa membatalkan pesanan sebelum admin konfirmasi pengiriman
     */
    public function cancelOrder($id)
    {
        try {
            // Cari transaksi milik user yang login
            $transaksi = Transaksi::where('id', $id)
                                 ->where('user_id', auth()->id())
                                 ->firstOrFail();

            // Cek apakah sudah ada shipping order
            $shippingOrder = ShippingOrder::where('transaksi_id', $transaksi->id)->first();

            // Tidak bisa cancel jika tidak ada shipping order
            if (!$shippingOrder) {
                return redirect()->back()->with('error', 'Pesanan tidak dapat dibatalkan karena data pengiriman tidak ditemukan.');
            }

            // Tidak bisa cancel jika sudah shipped atau delivered
            if (in_array($shippingOrder->status, ['shipped', 'delivered'])) {
                return redirect()->back()->with('error', 'Pesanan tidak dapat dibatalkan karena sudah dikirim atau sudah sampai.');
            }

            // Hanya bisa cancel jika status pending
            if ($shippingOrder->status !== 'pending') {
                return redirect()->back()->with('error', 'Pesanan tidak dapat dibatalkan karena sudah diproses admin.');
            }

            // Update status shipping menjadi cancelled
            $shippingOrder->update([
                'status' => 'cancelled',
                'notes' => 'Dibatalkan oleh customer pada ' . now()->format('d/m/Y H:i:s')
            ]);

            // Update status transaksi menjadi cancelled
            $transaksi->update(['status' => 'cancelled']);
            
            Log::info('Pesanan dibatalkan oleh customer', ['transaksi_id' => $transaksi->id, 'user_id' => auth()->id()]);
            
            return redirect()->back()->with('success', 'Pesanan #' . $transaksi->id . ' berhasil dibatalkan.');

        } catch (\Exception $e) {
            Log::error('Error saat membatalkan pesanan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membatalkan pesanan. Silakan coba lagi.');
        }
    }

    /**
     * Download PDF laporan transaksi - untuk customer yang ingin download ulang
     * Customer bisa download ulang laporan transaksi mereka
     */
    public function downloadLaporan($transaksi_id)
    {
        // Cari transaksi milik user yang login
        $transaksi = Transaksi::where('id', $transaksi_id)
                             ->where('user_id', auth()->id())
                             ->firstOrFail();

        // Cek apakah file PDF ada di storage
        $fullPdfPath = storage_path('app/public/' . $transaksi->pdf_path);
        
        if ($transaksi->pdf_path && File::exists($fullPdfPath)) {
            // Gunakan Response::download() yang benar
            return Response::download($fullPdfPath, 'laporan-transaksi-' . $transaksi->id . '.pdf');
        }

        return redirect()->back()->with('error', 'File laporan tidak ditemukan. Silakan hubungi admin.');
    }
}