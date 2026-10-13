{{-- Variabel: $timeline, $hours, $date, $canBook (bool) --}}
<div class="legend"><span><i style="background:var(--ok)"></i>Disetujui</span><span><i style="background:var(--wait)"></i>Menunggu persetujuan</span><span><i style="background:#fff;border:1px solid var(--line)"></i>Kosong</span></div>
<div class="tl-scroll">
    <div class="tl-head"><div class="tl-name">Ruangan</div>
        <div class="tl-hours">@foreach($hours as $i => $h)@if(! $loop->last)<span>{{ sprintf('%02d', $h) }}</span>@endif @endforeach</div>
    </div>
    @forelse($timeline as $row)
        <div class="tl-row">
            <div class="tl-name">{{ $row['room']->name }}<small>{{ $row['room']->capacity }} orang</small></div>
            <div>
                <div class="tl-bar">
                    @foreach($row['items'] as $it)
                        @php $b = $it['booking']; @endphp
                        <div class="tl-blk {{ $b->status }}" style="left:{{ $it['left'] }}%;width:{{ $it['width'] }}%"
                             title="{{ $b->time_label }} · {{ $b->status_label }}{{ $it['detail'] ? ' · '.$b->purpose : '' }}">
                            {{ $b->time_label }}{{ $it['detail'] ? ' '.$b->purpose : '' }}
                        </div>
                    @endforeach
                </div>
                @if($row['items']->isEmpty())
                    <span class="mute">Kosong sepanjang hari.</span>
                @else
                    <span class="mute">Terisi: {{ $row['items']->map(fn ($i) => $i['booking']->time_label)->implode(', ') }}</span>
                @endif
                @if($canBook)
                    · <a href="{{ route('booking.create', ['room' => $row['room']->id, 'date' => $date]) }}">Ajukan ruangan ini</a>
                @endif
            </div>
        </div>
    @empty
        <p class="mute">Belum ada ruangan aktif.</p>
    @endforelse
</div>
