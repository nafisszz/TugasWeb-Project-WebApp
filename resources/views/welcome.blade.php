@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
<section class="hero">
    <h1>Pinjam ruang kampus tanpa surat dan tanpa bentrok</h1>
    <p>Lihat ruangan yang kosong, ajukan peminjaman, dan pantau statusnya dari satu tempat. Sistem otomatis menolak jadwal yang beririsan.</p>
    <a class="btn" href="{{ route('login') }}">Masuk</a>
    <a class="btn ghost" href="{{ route('register') }}">Daftar akun</a>
</section>
<h2 style="margin-top:0">Ketersediaan hari ini</h2>
<div class="card">
    @include('partials.timeline', ['canBook' => false])
</div>
<p class="mute"><a href="{{ route('availability') }}">Lihat tanggal lain →</a></p>
@endsection
