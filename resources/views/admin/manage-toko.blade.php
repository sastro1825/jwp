@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Permohonan Toko Pending</h1>
    @foreach($tokos as $toko)
    <div class="card">
        <h5>{{ $toko->nama }} - Pemilik: {{ $toko->user->name }}</h5>
        <form action="{{ route('admin.toko.approve', $toko->id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-success">Approve</button>
        </form>
        <form action="{{ route('admin.toko.reject', $toko->id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-danger">Reject</button>
        </form>
    </div>
    @endforeach
</div>
@endsection