<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Transaksi;

class LaporanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfPath; // Path file PDF laporan (bisa null)
    public $transaksi; // Data transaksi untuk email

    /**
     * Create a new message instance - untuk kirim laporan PDF ke customer
     * 
     * @param string|null $pdfPath - Path ke file PDF laporan (bisa null jika PDF gagal generate)
     * @param Transaksi $transaksi - Data transaksi customer
     */
    public function __construct($pdfPath, Transaksi $transaksi)
    {
        $this->pdfPath = $pdfPath; // Simpan path PDF untuk attachment (bisa null)
        $this->transaksi = $transaksi; // Simpan data transaksi untuk template email
    }

    /**
     * Build the message - untuk email laporan pembelian menggunakan view yang sudah ada
     * Mengirim email dengan attachment PDF laporan (jika ada)
     */
    public function build()
    {
        $email = $this->subject('Laporan Pembelian Tukupedia - Transaksi #' . $this->transaksi->id)
                      ->view('emails.laporan') // Gunakan view yang sudah ada
                      ->with([
                          'transaksi' => $this->transaksi, // Pass data transaksi ke view
                          'customer' => $this->transaksi->user // Pass data customer ke view
                      ]);

        // Attach file PDF laporan jika file ada dan valid
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $email->attach($this->pdfPath, [
                'as' => 'laporan-pembelian-' . $this->transaksi->id . '.pdf', // Nama file attachment
                'mime' => 'application/pdf', // MIME type untuk PDF
            ]);
        }

        return $email;
    }
}