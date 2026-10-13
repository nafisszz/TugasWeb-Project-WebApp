<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filtered($request)->with(['user', 'room'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('booking_date')->orderBy('start_time');

        $counts = Booking::selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('admin.bookings.index', [
            'bookings' => $query->paginate(15)->withQueryString(),
            'rooms' => Room::orderBy('name')->get(),
            'counts' => $counts,
            'filters' => $request->only(['status', 'room', 'from', 'to', 'q']),
        ]);
    }

    public function approve(Booking $booking, BookingService $service): RedirectResponse
    {
        $this->authorize('review', $booking);

        return $this->run(fn () => $service->approve($booking), 'Pengajuan disetujui.');
    }

    public function reject(Request $request, Booking $booking, BookingService $service): RedirectResponse
    {
        $this->authorize('review', $booking);
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:255']]);

        return $this->run(fn () => $service->reject($booking, $data['admin_note'] ?? null), 'Pengajuan ditolak.');
    }

    public function cancel(Request $request, Booking $booking, BookingService $service): RedirectResponse
    {
        $this->authorize('cancel', $booking);
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:255']]);

        return $this->run(fn () => $service->cancel($booking, $data['admin_note'] ?? null), 'Pengajuan dibatalkan.');
    }

    /** Rekap CSV sesuai filter yang sedang aktif (bisa dibuka di Excel). */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->with(['user', 'room'])->orderBy('booking_date')->orderBy('start_time');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            fputcsv($out, ['Tanggal', 'Mulai', 'Selesai', 'Ruangan', 'Peminjam', 'Organisasi', 'Keperluan', 'Status', 'Catatan admin']);
            $query->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $b) {
                    fputcsv($out, [
                        $b->booking_date, substr($b->start_time, 0, 5), substr($b->end_time, 0, 5),
                        $b->room->name, $b->user->name, $b->user->organisasi, $b->purpose,
                        $b->status_label, $b->admin_note,
                    ]);
                }
            });
            fclose($out);
        }, 'rekap-peminjaman-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request): Builder
    {
        $request->validate([
            'status' => ['nullable', 'in:' . implode(',', array_keys(Booking::STATUSES))],
            'room' => ['nullable', 'integer'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        return Booking::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('room'), fn ($q) => $q->where('room_id', $request->room))
            ->when($request->filled('from'), fn ($q) => $q->where('booking_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->where('booking_date', '<=', $request->to))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(function ($w) use ($term) {
                    $w->where('purpose', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('organisasi', 'like', $term));
                });
            });
    }

    private function run(callable $action, string $okMessage): RedirectResponse
    {
        try {
            $action();
        } catch (BookingException $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', $okMessage);
    }
}
