<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /** Daftar jam untuk pilihan form, mis. ['07:00','07:30',...,'21:00']. */
    public static function slots(): array
    {
        $step = (int) config('ruangku.step_minutes');
        $out = [];
        for ($m = config('ruangku.open_hour') * 60; $m <= config('ruangku.close_hour') * 60; $m += $step) {
            $out[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
        }

        return $out;
    }

    public static function minutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));

        return $h * 60 + $m;
    }

    /** Jam-jam bulat untuk header timeline. */
    public static function hours(): array
    {
        return range(config('ruangku.open_hour'), config('ruangku.close_hour'));
    }

    /**
     * Timeline seluruh ruangan aktif pada satu tanggal.
     * Setiap blok berisi posisi (left/width dalam persen) untuk digambar di CSS.
     */
    public function timeline(string $date, ?User $viewer = null): Collection
    {
        $open = config('ruangku.open_hour') * 60;
        $span = (config('ruangku.close_hour') * 60) - $open;

        $bookings = Booking::query()
            ->with('user:id,name,organisasi')
            ->where('booking_date', $date)
            ->blocking()
            ->orderBy('start_time')
            ->get()
            ->groupBy('room_id');

        return Room::query()->active()->orderBy('name')->get()->map(function (Room $room) use ($bookings, $open, $span, $viewer) {
            $items = $bookings->get($room->id, collect())->map(function (Booking $b) use ($open, $span, $viewer) {
                $s = self::minutes($b->start_time);
                $e = self::minutes($b->end_time);

                return [
                    'booking' => $b,
                    'left' => round(($s - $open) / $span * 100, 2),
                    'width' => round(($e - $s) / $span * 100, 2),
                    // Detail kegiatan hanya untuk pemilik dan admin
                    'detail' => $viewer && ($viewer->isAdmin() || $viewer->id === $b->user_id),
                ];
            });

            return ['room' => $room, 'items' => $items];
        });
    }
}
