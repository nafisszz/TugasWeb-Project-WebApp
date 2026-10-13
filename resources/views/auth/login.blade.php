@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<div class="card narrow"><h1>Masuk</h1>
<form method="post" action="{{ route('login') }}" class="stack">@csrf
    <label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus>
        @error('email')<span class="field-err">{{ $message }}</span>@enderror</label>
    <label>Kata sandi<input type="password" name="password" required>
        @error('password')<span class="field-err">{{ $message }}</span>@enderror</label>
    <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
    <button>Masuk</button>
</form>
<p class="mute">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p></div>
@endsection
