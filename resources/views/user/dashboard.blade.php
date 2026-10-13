@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
<h1>Halo, {{ auth()->user()->name }}</h1>
<div class="stats">
    <div class="stat"><b>{{ $stats['pending'] }}</b><span>Menunggu persetujuan</span></div>
    <div class="stat"><b>{{ $stats['upcoming'] }}</b><span>Disetujui (akan datang)</span></div>
    <div class="stat"><b>{{ $stats['total'] }}</b><span>Total pengajuan</span></div>
</div>

<h2>Ruangan</h2>
<div class="grid">
@foreach($rooms as $r)
    <div class="card">
        <h3>{{ $r->name }}</h3>
        <p style="margin:0">Kapasitas {{ $r->capacity }} orang</p>
        <p class="mute" style="margin:2px 0 10px">{{ $r->facilities }}</p>
        <p style="margin:0 0 10px">@if(in_array($r->id, $busyToday))<span class="tag pending">Ada jadwal hari ini</span>@else<span class="tag on">Kosong hari ini</span>@endif</p>
        <a class="btn sm" href="{{ route('booking.create', ['room' => $r->id]) }}">Ajukan</a>
    </div>
@endforeach
</div>

<h2>Pengajuan saya</h2>
@if($bookings->isEmpty())
    <p class="mute">Belum ada pengajuan. Pilih ruangan di atas untuk mulai.</p>
@else
<div class="scroll"><table>
<tr><th>Ruangan</th><th>Waktu</th><th>Keperluan</th><th>Status</th><th></th></tr>
@foreach($bookings as $b)
<tr>
    <td>{{ $b->room->name }}</td>
    <td>{{ $b->date_label }}<br>{{ $b->time_label }}</td>
    <td>{{ $b->purpose }}@if($b->admin_note)<br><span class="mute">Catatan admin: {{ $b->admin_note }}</span>@endif</td>
    <td>@include('partials.badge', ['status' => $b->status])</td>
    <td>@can('cancel', $b)
        <form method="post" action="{{ route('booking.cancel', $b) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">@csrf<button class="sm no">Batalkan</button></form>
    @endcan</td>
</tr>
@endforeach
</table></div>
{{ $bookings->links('pagination.ruangku') }}
@endif
@endsection
