<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Keranjang;
use App\Models\Transaksi;
use App\Models\Produk;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\LaporanMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    // Hapus constructor dengan middleware
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('role:customer');
    // }

    public function tambahKeKeranjang(Request $request, $produk_id)
    {
        $produk = Produk::findOrFail($produk_id);
        $keranjang = Keranjang::updateOrCreate(
            ['user_id' => auth()->id(), 'produk_id' => $produk_id],
            ['jumlah' => $request->jumlah ?? 1]
        );
        return redirect()->route('keranjang')->with('success', 'Ditambahkan ke keranjang.');
    }

    public function keranjang()
    {
        $items = Keranjang::where('user_id', auth()->id())->with('produk')->get();
        $total = $items->sum(fn($item) => $item->jumlah * $item->produk->harga);
        return view('keranjang-belanja', compact('items', 'total'));
    }

    public function checkout(Request $request)
    {
        $request->validate(['metode_pembayaran' => 'required|in:prepaid,postpaid']);

        $items = Keranjang::where('user_id', auth()->id())->with('produk')->get();
        if ($items->isEmpty()) {
            return redirect()->route('keranjang')->with('error', 'Keranjang kosong.');
        }

        $total = $items->sum(fn($item) => $item->jumlah * $item->produk->harga);
        $transaksi = Transaksi::create([
            'user_id' => auth()->id(),
            'total' => $total + ($total * 0.1), // +10% pajak seperti SRS
            'metode_pembayaran' => $request->metode_pembayaran,
        ]);

        $pdf = Pdf::loadView('laporan-pembelian', compact('transaksi', 'items', 'total'));
        $pdfPath = 'laporan/transaksi-' . $transaksi->id . '.pdf';
        $pdf->save(storage_path('app/public/' . $pdfPath));
        $transaksi->update(['pdf_path' => $pdfPath]);

        Mail::to(auth()->user()->email)->send(new LaporanMail(storage_path('app/public/' . $pdfPath)));

        Keranjang::where('user_id', auth()->id())->delete();

        return redirect()->route('home')->with('success', 'Pembelian selesai! Laporan dikirim ke email.');
    }
}