@extends('layouts.app')
@section('title', 'Ketersediaan')
@section('content')
<h1>Kalender ketersediaan</h1>
<div class="toolbar">
    <div>
        <a class="btn ghost sm" href="{{ route('availability', ['date' => $prev]) }}">← Sebelumnya</a>
        <a class="btn ghost sm" href="{{ route('availability', ['date' => $next]) }}">Berikutnya →</a>
        <a class="btn ghost sm" href="{{ route('availability') }}">Hari ini</a>
    </div>
    <form method="get" style="display:flex;gap:8px;align-items:center">
        <input type="date" name="date" value="{{ $date }}" style="width:auto"><button class="sm">Lihat</button>
    </form>
</div>
<p><strong>{{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y') }}</strong></p>
<div class="card">
    @include('partials.timeline', ['canBook' => auth()->check() && ! auth()->user()->isAdmin()])
</div>
@endsection
