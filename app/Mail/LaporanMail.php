<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LaporanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfPath; // Path file PDF laporan

    /**
     * Create a new message instance - untuk kirim laporan PDF
     */
    public function __construct($pdfPath)
    {
        $this->pdfPath = $pdfPath; // Simpan path PDF untuk attachment
    }

    /**
     * Build the message - untuk email laporan
     */
    public function build()
    {
        return $this->subject('Laporan Pembelian OSS - Toko Alat Kesehatan') // Subject email
                    ->view('emails.laporan') // View template email  
                    ->attach($this->pdfPath, [ // Attach file PDF
                        'as' => 'laporan-pembelian.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}