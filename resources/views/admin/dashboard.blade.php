@extends('layouts.app')
@section('title', 'Dasbor admin')
@section('content')
<h1>Dasbor admin</h1>
<div class="stats">
    <div class="stat"><b>{{ $stats['pending'] }}</b><span>Menunggu persetujuan</span></div>
    <div class="stat"><b>{{ $stats['today'] }}</b><span>Kegiatan disetujui hari ini</span></div>
    <div class="stat"><b>{{ $stats['rooms'] }}</b><span>Ruangan aktif</span></div>
    <div class="stat"><b>{{ $stats['total'] }}</b><span>Total pengajuan</span></div>
</div>

<h2>Perlu diproses</h2>
@if($pending->isEmpty())<p class="mute">Tidak ada pengajuan yang menunggu. 🎉</p>
@else
<div class="scroll"><table>
<tr><th>Peminjam</th><th>Ruangan</th><th>Waktu</th><th>Keperluan</th><th></th></tr>
@foreach($pending as $b)
<tr><td>{{ $b->user->name }}<br><span class="mute">{{ $b->user->organisasi }}</span></td><td>{{ $b->room->name }}</td>
<td>{{ $b->date_label }}<br>{{ $b->time_label }}</td><td>{{ $b->purpose }}</td>
<td>@include('admin.bookings._actions', ['b' => $b])</td></tr>
@endforeach
</table></div>
<p class="mute"><a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}">Lihat semua yang menunggu →</a></p>
@endif

<h2>Jadwal disetujui hari ini</h2>
@if($schedule->isEmpty())<p class="mute">Belum ada kegiatan hari ini.</p>
@else
<div class="scroll"><table>
<tr><th>Jam</th><th>Ruangan</th><th>Peminjam</th><th>Keperluan</th></tr>
@foreach($schedule as $b)<tr><td>{{ $b->time_label }}</td><td>{{ $b->room->name }}</td><td>{{ $b->user->name }}</td><td>{{ $b->purpose }}</td></tr>@endforeach
</table></div>
@endif
@endsection
