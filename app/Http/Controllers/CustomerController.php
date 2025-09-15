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
        
        return view('keranjang-belanja', compact('items', 'subtotal', 'pajak', 'total')); // Gunakan view yang sudah ada
    }

    /**
     * Proses checkout pembelian - untuk kategori saja
     * Membuat transaksi dan generate PDF laporan
     */
    public function checkout(Request $request)
    {
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
            return redirect()->route('customer.keranjang')->with('error', 'Keranjang kosong. Tambahkan kategori terlebih dahulu.');
        }

        // Hitung total dengan pajak 10% sesuai SRS
        $subtotal = $items->sum(function($item) {
            return $item->jumlah * $item->harga;
        });
        $pajak = $subtotal * 0.1; // 10% pajak
        $total = $subtotal + $pajak;

        // Buat transaksi baru di database
        $transaksi = Transaksi::create([
            'user_id' => auth()->id(),
            'total' => $total,
            'metode_pembayaran' => $request->metode_pembayaran,
            'status' => 'completed'
        ]);

        // Siapkan data untuk PDF laporan
        $user = auth()->user();
        $dataLaporan = [
            'transaksi' => $transaksi,
            'user' => $user,
            'items' => $items, // Items dari kategori
            'subtotal' => $subtotal,
            'pajak' => $pajak,
            'total' => $total
        ];

        try {
            // Pastikan direktori laporan ada
            $laporanDir = storage_path('app/public/laporan');
            if (!File::exists($laporanDir)) {
                File::makeDirectory($laporanDir, 0755, true);
            }

            // Generate PDF laporan pembelian sesuai SRS requirement
            $pdf = Pdf::loadView('laporan-pembelian', $dataLaporan); // Gunakan view yang sudah ada
            $pdfFileName = 'transaksi-' . $transaksi->id . '.pdf';
            $pdfPath = 'laporan/' . $pdfFileName;
            $fullPdfPath = storage_path('app/public/' . $pdfPath);

            // Simpan PDF ke storage
            $pdf->save($fullPdfPath);
            
            // Update transaksi dengan path PDF
            $transaksi->update(['pdf_path' => $pdfPath]);

            // Kirim laporan ke email customer sesuai SRS
            try {
                Mail::to($user->email)->send(new LaporanMail($fullPdfPath, $transaksi));
            } catch (\Exception $e) {
                // Log error email tapi lanjutkan proses
                \Log::error('Email laporan gagal dikirim: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            // Log error PDF generation
            \Log::error('PDF generation failed: ' . $e->getMessage());
            return redirect()->route('customer.keranjang')->with('error', 'Terjadi kesalahan saat memproses transaksi. Silakan coba lagi.');
        }

        // Kosongkan keranjang setelah checkout berhasil
        Keranjang::where('user_id', auth()->id())->delete();

        return redirect()->route('home')->with('success', 'Pembelian berhasil! Laporan telah dikirim ke email Anda. ID Transaksi: ' . $transaksi->id);
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
     * View account detail - customer view account detail
     * Menampilkan detail akun customer dan riwayat transaksi
     */
    public function viewAccount()
    {
        $user = auth()->user();
        
        // Ambil riwayat transaksi customer dengan pagination
        $transaksis = Transaksi::where('user_id', $user->id)
                              ->with(['shippingOrder'])
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);
        
        // Ambil feedback yang sudah dikirim customer
        $feedbacks = GuestBook::where('email', $user->email)
                             ->orderBy('created_at', 'desc')
                             ->limit(5)
                             ->get();

        return view('customer.account', compact('user', 'transaksis', 'feedbacks'));
    }

    /**
     * Cancel order sebelum dikirim - customer cancel order before shipping
     * Customer bisa membatalkan pesanan sebelum barang dikirim
     */
    public function cancelOrder($id)
    {
        // Cari transaksi milik user yang login
        $transaksi = Transaksi::where('id', $id)
                             ->where('user_id', auth()->id())
                             ->firstOrFail();

        // Cek apakah sudah ada shipping order
        $shippingOrder = ShippingOrder::where('transaksi_id', $transaksi->id)->first();

        // Tidak bisa cancel jika sudah shipped atau delivered
        if ($shippingOrder && in_array($shippingOrder->status, ['shipped', 'delivered'])) {
            return redirect()->back()->with('error', 'Pesanan tidak dapat dibatalkan karena sudah dikirim.');
        }

        // Update status shipping jika ada menjadi cancelled
        if ($shippingOrder) {
            $shippingOrder->update(['status' => 'cancelled']);
        }

        // Update status transaksi menjadi cancelled
        $transaksi->update(['status' => 'cancelled']);
        
        return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan.');
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