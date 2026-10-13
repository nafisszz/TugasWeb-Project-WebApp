<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /** Pemilik atau admin boleh membatalkan selama masih menunggu/disetujui dan tanggalnya belum lewat. */
    public function cancel(User $user, Booking $booking): bool
    {
        return ($user->isAdmin() || $user->id === $booking->user_id)
            && in_array($booking->status, [Booking::PENDING, Booking::APPROVED], true)
            && $booking->isUpcomingOrToday();
    }

    /** Hanya admin yang boleh menyetujui/menolak, dan hanya pengajuan yang masih menunggu. */
    public function review(User $user, Booking $booking): bool
    {
        return $user->isAdmin() && $booking->status === Booking::PENDING;
    }
}
