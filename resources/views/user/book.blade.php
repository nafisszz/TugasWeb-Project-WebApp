@extends('layouts.app')
@section('title', 'Ajukan peminjaman')
@section('content')
<div class="card wide" style="margin:0 auto"><h1>Ajukan peminjaman</h1>
@error('slot')<p class="flash err">{{ $message }}</p>@enderror
<form method="post" action="{{ route('booking.store') }}" class="stack" id="bookForm">@csrf
    <label>Ruangan
        <select name="room_id" id="fRoom">
            @foreach($rooms as $r)<option value="{{ $r->id }}" @selected($roomId == $r->id)>{{ $r->name }} ({{ $r->capacity }} orang)</option>@endforeach
        </select>@error('room_id')<span class="field-err">{{ $message }}</span>@enderror</label>
    <label>Tanggal
        <input type="date" name="booking_date" id="fDate" min="{{ today()->toDateString() }}" max="{{ $maxDate }}" value="{{ $date }}" required>
        @error('booking_date')<span class="field-err">{{ $message }}</span>@enderror</label>

    <div class="mute" style="background:var(--bg);border-radius:8px;padding:10px 12px">
        <strong>Jadwal terisi pada tanggal ini:</strong>
        @if($busy->isEmpty()) belum ada, ruangan kosong.
        @else
            <ul style="margin:4px 0 0;padding-left:18px">
            @foreach($busy as $b)<li>{{ $b->time_label }} — {{ $b->status_label }}</li>@endforeach
            </ul>
        @endif
    </div>

    <div class="row2">
        <label>Mulai
            <select name="start">@foreach($slots as $s)@if(! $loop->last)<option value="{{ $s }}" @selected($start === $s)>{{ $s }}</option>@endif @endforeach</select>
            @error('start')<span class="field-err">{{ $message }}</span>@enderror</label>
        <label>Selesai
            <select name="end">@foreach($slots as $s)@if(! $loop->first)<option value="{{ $s }}" @selected($end === $s)>{{ $s }}</option>@endif @endforeach</select>
            @error('end')<span class="field-err">{{ $message }}</span>@enderror</label>
    </div>
    <label>Keperluan
        <input name="purpose" value="{{ old('purpose') }}" maxlength="255" placeholder="Contoh: Rapat kerja HIMA" required>
        @error('purpose')<span class="field-err">{{ $message }}</span>@enderror</label>
    <p class="mute" style="margin:0">Maksimal {{ config('ruangku.max_hours') }} jam per pengajuan. Jam operasional {{ sprintf('%02d:00', config('ruangku.open_hour')) }}–{{ sprintf('%02d:00', config('ruangku.close_hour')) }}.</p>
    <button>Kirim pengajuan</button>
</form></div>
<script>
// Ganti ruangan/tanggal -> muat ulang halaman agar daftar jadwal terisi ikut berubah.
(function () {
    var r = document.getElementById('fRoom'), d = document.getElementById('fDate');
    function reload() {
        if (!d.value) return;
        location.href = "{{ route('booking.create') }}?room=" + encodeURIComponent(r.value) + "&date=" + encodeURIComponent(d.value);
    }
    r.addEventListener('change', reload); d.addEventListener('change', reload);
})();
</script>
@endsection
