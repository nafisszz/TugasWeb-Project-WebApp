<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Membuat pengajuan baru. Pengecekan bentrok dilakukan di dalam transaksi
     * dengan mengunci baris ruangan (SELECT ... FOR UPDATE), sehingga dua
     * pengguna yang mengajukan bersamaan tidak bisa sama-sama lolos.
     *
     * @param  array{room_id:int,booking_date:string,start:string,end:string,purpose:string}  $data  start/end format H:i
     */
    public function create(User $user, array $data): Booking
    {
        $start = $data['start'] . ':00';
        $end = $data['end'] . ':00';

        return DB::transaction(function () use ($user, $data, $start, $end) {
            $room = Room::query()->active()->whereKey($data['room_id'])->lockForUpdate()->first();
            if (! $room) {
                throw new BookingException('Ruangan tidak tersedia atau sedang dinonaktifkan.');
            }

            $conflict = Booking::query()
                ->where('room_id', $room->id)
                ->where('booking_date', $data['booking_date'])
                ->blocking()
                ->overlapping($start, $end)
                ->first();

            if ($conflict) {
                throw new BookingException(sprintf(
                    'Jadwal bentrok dengan kegiatan lain (%s). Pilih jam atau ruangan lain.',
                    $conflict->time_label
                ));
            }

            return Booking::create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'booking_date' => $data['booking_date'],
                'start_time' => $start,
                'end_time' => $end,
                'purpose' => $data['purpose'],
                'status' => Booking::PENDING,
            ]);
        });
    }

    public function approve(Booking $booking): void
    {
        $this->transition($booking, Booking::PENDING, Booking::APPROVED);
    }

    public function reject(Booking $booking, ?string $note = null): void
    {
        $this->transition($booking, Booking::PENDING, Booking::REJECTED, $note);
    }

    public function cancel(Booking $booking, ?string $note = null): void
    {
        $this->transition($booking, [Booking::PENDING, Booking::APPROVED], Booking::CANCELLED, $note);
    }

    /** Ubah status dengan penguncian baris agar dua admin tidak memproses pengajuan yang sama. */
    private function transition(Booking $booking, string|array $from, string $to, ?string $note = null): void
    {
        DB::transaction(function () use ($booking, $from, $to, $note) {
            $fresh = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            if (! in_array($fresh->status, (array) $from, true)) {
                throw new BookingException('Pengajuan ini sudah diproses atau statusnya sudah berubah.');
            }
            $fresh->status = $to;
            if ($note !== null && $note !== '') {
                $fresh->admin_note = $note;
            }
            $fresh->save();
        });
    }
}
