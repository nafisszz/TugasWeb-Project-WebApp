@extends('layouts.app')
@section('title', 'Kelola ruangan')
@section('content')
<div class="toolbar"><h1 style="margin:0">Kelola ruangan</h1><a class="btn" href="{{ route('admin.rooms.create') }}">+ Tambah ruangan</a></div>
<div class="scroll"><table>
<tr><th>Nama</th><th>Kapasitas</th><th>Fasilitas</th><th>Riwayat</th><th>Status</th><th></th></tr>
@foreach($rooms as $r)
<tr><td>{{ $r->name }}</td><td>{{ $r->capacity }}</td><td>{{ $r->facilities }}</td><td>{{ $r->bookings_count }}</td>
<td><span class="tag {{ $r->active ? 'on' : 'off' }}">{{ $r->active ? 'Aktif' : 'Nonaktif' }}</span></td>
<td>
    <a class="btn ghost sm" href="{{ route('admin.rooms.edit', $r) }}">Ubah</a>
    <form method="post" action="{{ route('admin.rooms.toggle', $r) }}">@csrf<button class="sm">{{ $r->active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
    @if($r->bookings_count == 0)
    <form method="post" action="{{ route('admin.rooms.destroy', $r) }}" onsubmit="return confirm('Hapus ruangan ini?')">@csrf @method('DELETE')<button class="sm no">Hapus</button></form>
    @endif
</td></tr>
@endforeach
</table></div>
@endsection
