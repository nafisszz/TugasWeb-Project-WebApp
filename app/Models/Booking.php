<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';

    public const STATUSES = [
        self::PENDING => 'Menunggu',
        self::APPROVED => 'Disetujui',
        self::REJECTED => 'Ditolak',
        self::CANCELLED => 'Dibatalkan',
    ];

    // booking_date sengaja TIDAK di-cast ke date: disimpan sebagai string 'Y-m-d'
    // supaya perbandingan di query konsisten di MySQL maupun SQLite (test).
    protected $fillable = [
        'user_id', 'room_id', 'booking_date', 'start_time', 'end_time',
        'purpose', 'status', 'admin_note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /** Pengajuan yang memblokir jadwal (menunggu atau disetujui). */
    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', [self::PENDING, self::APPROVED]);
    }

    /** Irisan jam: mulai baru < selesai lama DAN selesai baru > mulai lama. */
    public function scopeOverlapping(Builder $query, string $start, string $end): Builder
    {
        return $query->where('start_time', '<', $end)->where('end_time', '>', $start);
    }

    public function getDateLabelAttribute(): string
    {
        return Carbon::parse($this->booking_date)->locale('id')->translatedFormat('D, d M Y');
    }

    public function getTimeLabelAttribute(): string
    {
        return substr($this->start_time, 0, 5) . '–' . substr($this->end_time, 0, 5);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isUpcomingOrToday(): bool
    {
        return Carbon::parse($this->booking_date)->startOfDay()->gte(Carbon::today());
    }
}
