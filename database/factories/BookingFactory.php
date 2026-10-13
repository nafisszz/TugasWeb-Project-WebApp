<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Booking> */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'room_id' => Room::factory(),
            'booking_date' => now()->addDays(2)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'purpose' => 'Rapat koordinasi organisasi',
            'status' => Booking::PENDING,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => Booking::APPROVED]);
    }
}
