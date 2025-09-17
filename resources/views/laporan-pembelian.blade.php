<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian OSS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Tukupedia - Laporan Belanja Anda</div>
        <div>Toko Alat Kesehatan Online</div>
    </div>
    
    {{-- Informasi Customer --}}
    <p><strong>User ID:</strong> {{ $user->id }}</p>
    <p><strong>Nama:</strong> {{ $user->name }}</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Tanggal:</strong> {{ $transaksi->created_at->format('d/m/Y H:i') }}</p>
    <p><strong>No. Transaksi:</strong> {{ $transaksi->id }}</p>
    
    {{-- Tabel Item Pembelian dengan data yang benar --}}
    <table>
        <tr>
            <th>No</th>
            <th>Nama Item</th>
            <th>ID/Kode</th>
            <th>Tipe</th>
            <th>Jumlah</th>
            <th>Harga</th>
            <th>Subtotal</th>
        </tr>
        @foreach($items as $key => $item)
        <tr>
            <td>{{ $key + 1 }}</td>
            {{-- Perbaikan nama item --}}
            <td>{{ $item->nama_item ?? 'Item tidak diketahui' }}</td>
            <td>
                {{-- Perbaikan ID/Kode berdasarkan tipe item --}}
                @if($item->item_type === 'kategori')
                    KAT-{{ $loop->index + 1 }}
                @elseif($item->item_type === 'toko_kategori')
                    TOKO-{{ $loop->index + 1 }}
                @else
                    PROD-{{ $loop->index + 1 }}
                @endif
            </td>
            <td>
                {{-- Perbaikan tipe item --}}
                @if($item->item_type === 'kategori')
                    Kategori Admin
                @elseif($item->item_type === 'toko_kategori')
                    Kategori Toko
                @else
                    Produk
                @endif
            </td>
            <td class="text-right">{{ $item->jumlah ?? 1 }}</td>
            <td class="text-right">Rp. {{ number_format((float)($item->harga_item ?? 0), 0, ',', '.') }}</td>
            <td class="text-right">Rp. {{ number_format((float)($item->subtotal_item ?? 0), 0, ',', '.') }}</td>
        </tr>
        @endforeach
        
        {{-- Baris Total --}}
        <tr class="total-row">
            <td colspan="6" class="text-right"><strong>TOTAL:</strong></td>
            <td class="text-right"><strong>Rp. {{ number_format((float)$total, 0, ',', '.') }}</strong></td>
        </tr>
    </table>
    
    {{-- Informasi Pembayaran --}}
    <p><strong>Cara Bayar:</strong> {{ $transaksi->metode_pembayaran == 'prepaid' ? 'Prepaid (Kartu/PayPal)' : 'Postpaid (COD)' }}</p>
    <p><strong>Status:</strong> {{ ucfirst($transaksi->status) }}</p>
    
    {{-- Catatan Penting --}}
    <div style="margin-top: 30px;">
        <h4>Catatan Penting:</h4>
        <ul>
            <li>Simpan laporan ini sebagai bukti pembelian yang sah</li>
            <li>Untuk pembayaran postpaid, siapkan uang pas saat barang tiba</li>
            <li>Barang akan diproses dalam 1-2 hari kerja</li>
            <li>Hubungi customer service: fajarstudent28@gmail.com</li>
        </ul>
    </div>
    
    {{-- Tanda Tangan --}}
    <div style="margin-top: 40px;">
        <p><strong>Tanda Tangan Toko:</strong> ________________</p>
        <p><strong>Tanda Tangan Customer:</strong> ________________</p>
    </div>
    
    {{-- Footer PDF --}}
    <div style="margin-top: 30px; text-align: center; font-size: 10px;">
        <p>Dokumen dibuat otomatis pada {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Tukupedia | fajarstudent28@gmail.com</p>
    </div>
</body>
</html>