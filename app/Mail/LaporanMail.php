<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Transaksi;

class LaporanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfPath; // Path file PDF laporan
    public $transaksi; // Data transaksi untuk email

    /**
     * Create a new message instance - untuk kirim laporan PDF ke customer
     * 
     * @param string $pdfPath - Path ke file PDF laporan
     * @param Transaksi $transaksi - Data transaksi customer
     */
    public function __construct($pdfPath, Transaksi $transaksi)
    {
        $this->pdfPath = $pdfPath; // Simpan path PDF untuk attachment
        $this->transaksi = $transaksi; // Simpan data transaksi untuk template email
    }

    /**
     * Build the message - untuk email laporan pembelian
     * Mengirim email dengan attachment PDF laporan
     */
    public function build()
    {
        return $this->subject('Laporan Pembelian OSS - Transaksi #' . $this->transaksi->id) // Subject email dengan ID transaksi
                    ->view('emails.laporan') // View template email  
                    ->with([
                        'transaksi' => $this->transaksi, // Pass data transaksi ke view
                        'customer' => $this->transaksi->user // Pass data customer ke view
                    ])
                    ->attach($this->pdfPath, [ // Attach file PDF laporan
                        'as' => 'laporan-pembelian-' . $this->transaksi->id . '.pdf', // Nama file attachment
                        'mime' => 'application/pdf', // MIME type untuk PDF
                    ]);
    }
}