@if ($paginator->hasPages())
<div class="pager">
    @if ($paginator->onFirstPage())<span class="mute">← Sebelumnya</span>@else<a href="{{ $paginator->previousPageUrl() }}">← Sebelumnya</a>@endif
    <span class="mute">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
    @if ($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}">Berikutnya →</a>@else<span class="mute">Berikutnya →</span>@endif
</div>
@endif
