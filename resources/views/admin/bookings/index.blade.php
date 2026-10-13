@extends('layouts.app')
@section('title', 'Pengajuan')
@section('content')
<div class="toolbar"><h1 style="margin:0">Pengajuan peminjaman</h1>
    <a class="btn ghost sm" href="{{ route('admin.bookings.export', $filters) }}">Unduh rekap CSV</a></div>

<div class="filters">
    <a href="{{ route('admin.bookings.index') }}" class="{{ empty($filters['status']) ? 'on' : '' }}">Semua</a>
    @foreach(\App\Models\Booking::STATUSES as $k => $label)
        <a href="{{ route('admin.bookings.index', array_merge($filters, ['status' => $k])) }}" class="{{ ($filters['status'] ?? '') === $k ? 'on' : '' }}">{{ $label }} ({{ $counts[$k] ?? 0 }})</a>
    @endforeach
</div>

<form method="get" class="filterbar">
    <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
    <label>Ruangan<select name="room"><option value="">Semua</option>@foreach($rooms as $r)<option value="{{ $r->id }}" @selected(($filters['room'] ?? '') == $r->id)>{{ $r->name }}</option>@endforeach</select></label>
    <label>Dari tanggal<input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
    <label>Sampai tanggal<input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
    <label>Cari<input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama, organisasi, keperluan"></label>
    <button>Terapkan</button>
</form>

@if($bookings->isEmpty())<p class="mute">Tidak ada pengajuan pada filter ini.</p>
@else
<div class="scroll"><table>
<tr><th>Peminjam</th><th>Ruangan</th><th>Waktu</th><th>Keperluan</th><th>Status</th><th></th></tr>
@foreach($bookings as $b)
<tr><td>{{ $b->user->name }}<br><span class="mute">{{ $b->user->organisasi }}</span></td><td>{{ $b->room->name }}</td>
<td>{{ $b->date_label }}<br>{{ $b->time_label }}</td>
<td>{{ $b->purpose }}@if($b->admin_note)<br><span class="mute">Catatan: {{ $b->admin_note }}</span>@endif</td>
<td>@include('partials.badge', ['status' => $b->status])</td>
<td>@include('admin.bookings._actions', ['b' => $b])</td></tr>
@endforeach
</table></div>
{{ $bookings->links('pagination.ruangku') }}
@endif
@endsection
