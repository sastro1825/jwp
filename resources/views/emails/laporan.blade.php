<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian Tukupedia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            background: #007bff;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .info-box {
            background: white;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    {{-- Bagian header email --}}
    <div class="header">
        <h1>Tukupedia</h1>
        <h2>Laporan Pembelian Anda</h2>
        <p>Toko Alat Kesehatan Online</p>
    </div>

    {{-- Bagian konten utama email --}}
    <div class="content">
        {{-- Menampilkan nama pelanggan --}}
        <h3>Halo {{ $customer->name }},</h3>
        
        <p>Terima kasih telah berbelanja di <strong>Tukupedia</strong>, toko alat kesehatan terpercaya online.</p>
        
        <p>Pembelian Anda telah berhasil diproses dan laporan detail transaksi telah dilampirkan dalam format PDF pada email ini.</p>

        {{-- Informasi detail transaksi --}}
        <div class="info-box">
            <h4>Informasi Pembelian:</h4>
            <ul>
                {{-- Menampilkan nomor transaksi --}}
                <li><strong>No. Transaksi:</strong> {{ $transaksi->id }}</li>
                {{-- Menampilkan tanggal pembelian dengan format Indonesia --}}
                <li><strong>Tanggal Pembelian:</strong> {{ $transaksi->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</li>
                {{-- Menampilkan nama pelanggan --}}
                <li><strong>Customer:</strong> {{ $customer->name }}</li>
                {{-- Menampilkan email pelanggan --}}
                <li><strong>Email:</strong> {{ $customer->email }}</li>
                {{-- Menampilkan total pembayaran dengan format Rupiah --}}
                <li><strong>Total Pembayaran:</strong> Rp {{ number_format($transaksi->total, 0, ',', '.') }}</li>
                {{-- Menampilkan metode pembayaran --}}
                <li><strong>Metode Pembayaran:</strong> {{ $transaksi->metode_pembayaran == 'prepaid' ? 'Prepaid (Kartu/PayPal)' : 'COD (Cash On Delivery)' }}</li>
            </ul>
        </div>

        {{-- Informasi langkah selanjutnya --}}
        <div class="info-box">
            <h4>Apa Selanjutnya?</h4>
            <ul>
                <li><strong>Laporan PDF</strong> sudah dilampirkan pada email ini</li>
                <li><strong>Pesanan</strong> akan diproses dalam 1-2 hari kerja</li>
                <li><strong>Pengiriman</strong> akan dilakukan setelah konfirmasi pembayaran (jika postpaid)</li>
                <li><strong>Tracking</strong> akan dikirimkan via email setelah barang dikirim</li>
            </ul>
        </div>

        <p><strong>Catatan Penting:</strong></p>
        <ul>
            <li>Simpan laporan PDF ini sebagai bukti pembelian</li>
            <li>Untuk pembayaran postpaid, harap siapkan uang pas saat barang tiba</li>
            <li>Hubungi customer service jika ada pertanyaan: fajarstudent28@gmail.com</li>
        </ul>

        <p>Jika ada pertanyaan atau kendala, jangan ragu untuk menghubungi tim customer service kami.</p>

        <p>Terima kasih atas kepercayaan Anda berbelanja di Tukupedia!</p>

        <p>Salam sehat,<br>
        <strong>Tim Tukupedia</strong></p>
    </div>

    {{-- Bagian footer email --}}
    <div class="footer">
        {{-- Menampilkan waktu pembuatan dokumen --}}
        <p>Dokumen dibuat otomatis pada {{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</p>
        <p>Tukupedia | fajarstudent28@gmail.com</p>
    </div>
</body>
</html>