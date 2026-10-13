<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today = today()->toDateString();

        $stats = [
            'pending' => Booking::where('status', Booking::PENDING)->count(),
            'today' => Booking::where('status', Booking::APPROVED)->where('booking_date', $today)->count(),
            'rooms' => Room::active()->count(),
            'total' => Booking::count(),
        ];

        $pending = Booking::with(['user', 'room'])->where('status', Booking::PENDING)
            ->orderBy('booking_date')->orderBy('start_time')->limit(10)->get();

        $schedule = Booking::with(['user', 'room'])->where('status', Booking::APPROVED)
            ->where('booking_date', $today)->orderBy('start_time')->get();

        return view('admin.dashboard', compact('stats', 'pending', 'schedule'));
    }
}
