@extends('layouts.app')
@section('title', 'Daftar')
@section('content')
<div class="card narrow"><h1>Daftar akun</h1>
<form method="post" action="{{ route('register') }}" class="stack">@csrf
    <label>Nama<input name="name" value="{{ old('name') }}" required>@error('name')<span class="field-err">{{ $message }}</span>@enderror</label>
    <label>Email<input type="email" name="email" value="{{ old('email') }}" required>@error('email')<span class="field-err">{{ $message }}</span>@enderror</label>
    <label>Organisasi atau program studi<input name="organisasi" value="{{ old('organisasi') }}" placeholder="Contoh: HIMA Informatika">@error('organisasi')<span class="field-err">{{ $message }}</span>@enderror</label>
    <label>Kata sandi (min. 8 karakter)<input type="password" name="password" required>@error('password')<span class="field-err">{{ $message }}</span>@enderror</label>
    <label>Ulangi kata sandi<input type="password" name="password_confirmation" required></label>
    <button>Daftar</button>
</form>
<p class="mute">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p></div>
@endsection
