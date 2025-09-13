@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Keranjang Belanja</h1>
    <table class="table">
        <thead>
            <tr><th>No</th><th>Produk</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach($items as $key => $item)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item->produk->nama }} ({{ $item->produk->id_produk }})</td>
                <td>{{ $item->jumlah }}</td>
                <td>Rp. {{ number_format($item->produk->harga, 0, ',', '.') }}</td>
                <td>Rp. {{ number_format($item->jumlah * $item->produk->harga, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr><td colspan="4"><strong>Total (sebelum pajak)</strong></td><td><strong>Rp. {{ number_format($total, 0, ',', '.') }}</strong></td></tr>
        </tbody>
    </table>
    <form action="{{ route('customer.checkout') }}" method="POST">
        @csrf
        <label>Metode Pembayaran:</label>
        <select name="metode_pembayaran" class="form-control">
            <option value="prepaid">Prepaid (Kartu Debit/Kredit/PayPal)</option>
            <option value="postpaid">Postpaid (Bayar di Tempat)</option>
        </select>
        <button type="submit" class="btn btn-success">Checkout</button>
    </form>
</div>
@endsection