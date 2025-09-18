<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Transaksi;

class LaporanMail extends Mailable
{
    use Queueable, SerializesModels;

    // Path file PDF laporan (bisa null)
    public $pdfPath;
    // Data transaksi untuk email
    public $transaksi;

    /** 
     * Inisialisasi instance untuk kirim laporan PDF ke customer
     * @param string|null $pdfPath Path ke file PDF laporan (bisa null jika PDF gagal generate)
     * @param Transaksi $transaksi Data transaksi customer
     */
    public function __construct($pdfPath, Transaksi $transaksi)
    {
        // Simpan path PDF untuk attachment (bisa null)
        $this->pdfPath = $pdfPath;
        // Simpan data transaksi untuk template email
        $this->transaksi = $transaksi;
    }

    /** 
     * Membangun email laporan pembelian dengan view dan attachment PDF (jika ada)
     * @return $this
     */
    public function build()
    {
        // Siapkan email dengan subjek dan view
        $email = $this->subject('Laporan Pembelian Tukupedia - Transaksi #' . $this->transaksi->id)
                      ->view('emails.laporan') // Gunakan view 'emails.laporan'
                      ->with([
                          // Kirim data transaksi ke view
                          'transaksi' => $this->transaksi,
                          // Kirim data customer ke view
                          'customer' => $this->transaksi->user
                      ]);

        // Tambahkan file PDF sebagai attachment jika file ada
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $email->attach($this->pdfPath, [
                // Nama file attachment
                'as' => 'laporan-pembelian-' . $this->transaksi->id . '.pdf',
                // MIME type untuk PDF
                'mime' => 'application/pdf',
            ]);
        }

        return $email;
    }
}