@extends('layouts.app')
@section('title', $room->exists ? 'Ubah ruangan' : 'Tambah ruangan')
@section('content')
<div class="card narrow"><h1>{{ $room->exists ? 'Ubah ruangan' : 'Tambah ruangan' }}</h1>
<form method="post" class="stack" action="{{ $room->exists ? route('admin.rooms.update', $room) : route('admin.rooms.store') }}">
    @csrf @if($room->exists) @method('PUT') @endif
    <label>Nama<input name="name" value="{{ old('name', $room->name) }}" required>@error('name')<span class="field-err">{{ $message }}</span>@enderror</label>
    <label>Kapasitas<input type="number" name="capacity" min="1" value="{{ old('capacity', $room->capacity) }}" required>@error('capacity')<span class="field-err">{{ $message }}</span>@enderror</label>
    <label>Fasilitas<input name="facilities" value="{{ old('facilities', $room->facilities) }}">@error('facilities')<span class="field-err">{{ $message }}</span>@enderror</label>
    <label class="check"><input type="checkbox" name="active" value="1" @checked(old('active', $room->active))> Aktif (bisa dipinjam)</label>
    <button>Simpan</button>
    <a href="{{ route('admin.rooms.index') }}" class="mute">Batal</a>
</form></div>
@endsection
