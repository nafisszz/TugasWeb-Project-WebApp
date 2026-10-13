<?php

namespace App\Http\Controllers;

use App\Exceptions\BookingException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Room;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function dashboard(Request $request, AvailabilityService $availability)
    {
        $user = $request->user();
        $today = today()->toDateString();

        $rooms = Room::active()->orderBy('name')->get();
        $busyToday = Booking::blocking()->where('booking_date', $today)->pluck('room_id')->unique()->all();

        $stats = [
            'pending' => $user->bookings()->where('status', Booking::PENDING)->count(),
            'upcoming' => $user->bookings()->where('status', Booking::APPROVED)->where('booking_date', '>=', $today)->count(),
            'total' => $user->bookings()->count(),
        ];

        $bookings = $user->bookings()->with('room')
            ->orderByDesc('booking_date')->orderByDesc('start_time')
            ->paginate(10);

        return view('user.dashboard', compact('rooms', 'busyToday', 'stats', 'bookings'));
    }

    public function create(Request $request)
    {
        $rooms = Room::active()->orderBy('name')->get();

        $roomId = (int) ($request->old('room_id', $request->query('room')) ?: ($rooms->first()->id ?? 0));
        $date = (string) $request->old('booking_date', $request->query('date', today()->toDateString()));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = today()->toDateString();
        }

        $busy = Booking::with('user:id,name')->where('room_id', $roomId)
            ->where('booking_date', $date)->blocking()->orderBy('start_time')->get();

        $slots = AvailabilityService::slots();

        return view('user.book', [
            'rooms' => $rooms,
            'roomId' => $roomId,
            'date' => $date,
            'busy' => $busy,
            'slots' => $slots,
            'start' => $request->old('start', $request->query('start', '08:00')),
            'end' => $request->old('end', $request->query('end', '10:00')),
            'maxDate' => today()->addDays((int) config('ruangku.max_days_ahead'))->toDateString(),
        ]);
    }

    public function store(StoreBookingRequest $request, BookingService $service): RedirectResponse
    {
        try {
            $service->create($request->user(), $request->validated());
        } catch (BookingException $e) {
            return back()->withInput()->withErrors(['slot' => $e->getMessage()]);
        }

        return redirect()->route('user.dashboard')->with('ok', 'Pengajuan terkirim. Menunggu persetujuan admin.');
    }

    public function cancel(Booking $booking, BookingService $service): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        try {
            $service->cancel($booking);
        } catch (BookingException $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', 'Pengajuan dibatalkan.');
    }
}
