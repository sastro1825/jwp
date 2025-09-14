<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian OSS</title>
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
    {{-- Header Email --}}
    <div class="header">
        <h1>OSS - Online Shopping System</h1>
        <h2>Laporan Pembelian Anda</h2>
        <p>Toko Alat Kesehatan</p>
    </div>

    {{-- Content Email --}}
    <div class="content">
        <h3>Halo {{ auth()->user()->name }},</h3>
        
        <p>Terima kasih telah berbelanja di <strong>OSS - Online Shopping System</strong>, toko alat kesehatan terpercaya.</p>
        
        <p>Pembelian Anda telah berhasil diproses dan laporan detail transaksi telah dilampirkan dalam format PDF pada email ini.</p>

        <div class="info-box">
            <h4>Informasi Pembelian:</h4>
            <ul>
                <li><strong>Tanggal Pembelian:</strong> {{ now()->format('d/m/Y H:i') }}</li>
                <li><strong>Customer:</strong> {{ auth()->user()->name }}</li>
                <li><strong>Email:</strong> {{ auth()->user()->email }}</li>
                <li><strong>Metode Pembayaran:</strong> {{ ucfirst(session('metode_pembayaran', 'Tidak diketahui')) }}</li>
            </ul>
        </div>

        <div class="info-box">
            <h4>Apa Selanjutnya?</h4>
            <ul>
                <li>📧 <strong>Laporan PDF</strong> sudah dilampirkan pada email ini</li>
                <li>📦 <strong>Pesanan</strong> akan diproses dalam 1-2 hari kerja</li>
                <li>🚚 <strong>Pengiriman</strong> akan dilakukan setelah konfirmasi pembayaran (jika postpaid)</li>
                <li>📱 <strong>Tracking</strong> akan dikirimkan via email setelah barang dikirim</li>
            </ul>
        </div>

        <p><strong>Catatan Penting:</strong></p>
        <ul>
            <li>Simpan laporan PDF ini sebagai bukti pembelian</li>
            <li>Untuk pembayaran postpaid, harap siapkan uang pas saat barang tiba</li>
            <li>Hubungi customer service jika ada pertanyaan</li>
        </ul>

        <p>Jika ada pertanyaan atau kendala, jangan ragu untuk menghubungi tim customer service kami.</p>

        <p>Terima kasih atas kepercayaan Anda berbelanja di OSS!</p>

        <p>Salam sehat,<br>
        <strong>Tim OSS - Online Shopping System</strong></p>
    </div>

    {{-- Footer Email --}}
    <div class="footer">
        <p>&copy; {{ date('Y') }} OSS - Online Shopping System. Toko Alat Kesehatan Terpercaya.</p>
        <p>Email: noreply@oss.com | Website: www.oss.com</p>
        <p><em>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</em></p>
    </div>
</body>
</html>