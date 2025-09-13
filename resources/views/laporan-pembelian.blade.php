<!DOCTYPE html>
<html>
<head><title>Laporan Pembelian OSS</title></head>
<body>
    <h1>Toko Alat Kesehatan - Laporan Belanja Anda</h1>
    <p>User ID: {{ auth()->user()->id }}</p>
    <p>Nama: {{ auth()->user()->name }}</p>
    <p>Email: {{ auth()->user()->email }}</p>
    <p>Tanggal: {{ now()->format('d/m/Y H:i') }}</p>
    <table border="1">
        <tr><th>No</th><th>Nama Produk (ID)</th><th>Jumlah</th><th>Harga</th></tr>
        @foreach($items as $key => $item)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $item->produk->nama }} ({{ $item->produk->id_produk }})</td>
            <td>{{ $item->jumlah }}</td>
            <td>Rp. {{ number_format($item->produk->harga, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr><td colspan="3">Total (termasuk pajak 10%)</td><td>Rp. {{ number_format($transaksi->total, 0, ',', '.') }}</td></tr>
    </table>
    <p>Cara Bayar: {{ $transaksi->metode_pembayaran == 'prepaid' ? 'Prepaid' : 'Postpaid' }}</p>
    <p>Tanda Tangan Toko: ________________</p>
</body>
</html>