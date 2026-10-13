@can('review', $b)
    <form method="post" action="{{ route('admin.bookings.approve', $b) }}">@csrf<button class="sm ok">Setujui</button></form>
    <form method="post" action="{{ route('admin.bookings.reject', $b) }}">@csrf
        <input name="admin_note" placeholder="Alasan (opsional)" maxlength="255">
        <button class="sm no">Tolak</button></form>
@elsecan('cancel', $b)
    <form method="post" action="{{ route('admin.bookings.cancel', $b) }}" onsubmit="return confirm('Batalkan pengajuan yang sudah disetujui ini?')">@csrf
        <input name="admin_note" placeholder="Alasan (opsional)" maxlength="255">
        <button class="sm no">Batalkan</button></form>
@endcan
