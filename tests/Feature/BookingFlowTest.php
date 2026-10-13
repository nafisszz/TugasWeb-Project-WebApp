<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function payload(Room $room, array $over = []): array
    {
        return array_merge([
            'room_id' => $room->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'start' => '09:00',
            'end' => '11:00',
            'purpose' => 'Rapat kerja organisasi',
        ], $over);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/beranda')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_guest_can_see_public_availability(): void
    {
        Room::factory()->create(['name' => 'Aula Tes']);
        $this->get('/')->assertOk()->assertSee('Aula Tes');
        $this->get('/ketersediaan')->assertOk()->assertSee('Aula Tes');
    }

    public function test_register_creates_user_role_and_ignores_role_input(): void
    {
        $this->post('/daftar', [
            'name' => 'Budi', 'email' => 'budi@example.com', 'organisasi' => 'BEM',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'role' => 'admin',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com', 'role' => 'user']);
        $this->assertAuthenticated();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();
        $this->post('/masuk', ['email' => $user->email, 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_submit_booking_as_pending(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $this->actingAs($user)->post('/ajukan', $this->payload($room))->assertRedirect(route('user.dashboard'));

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id, 'room_id' => $room->id,
            'start_time' => '09:00:00', 'end_time' => '11:00:00', 'status' => 'pending',
        ]);
    }

    public function test_overlapping_booking_is_rejected(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->approved()->create(['room_id' => $room->id, 'start_time' => '09:00:00', 'end_time' => '11:00:00']);

        $this->actingAs(User::factory()->create())
            ->post('/ajukan', $this->payload($room, ['start' => '10:00', 'end' => '12:00']))
            ->assertSessionHasErrors('slot');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_pending_booking_also_blocks_the_slot(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->create(['room_id' => $room->id, 'start_time' => '09:00:00', 'end_time' => '11:00:00']);

        $this->actingAs(User::factory()->create())
            ->post('/ajukan', $this->payload($room, ['start' => '08:00', 'end' => '10:00']))
            ->assertSessionHasErrors('slot');
    }

    public function test_back_to_back_booking_is_allowed(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->approved()->create(['room_id' => $room->id, 'start_time' => '09:00:00', 'end_time' => '11:00:00']);

        $this->actingAs(User::factory()->create())
            ->post('/ajukan', $this->payload($room, ['start' => '11:00', 'end' => '12:00']))
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_rejected_or_cancelled_booking_does_not_block(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->create(['room_id' => $room->id, 'status' => 'rejected']);
        Booking::factory()->create(['room_id' => $room->id, 'status' => 'cancelled']);

        $this->actingAs(User::factory()->create())
            ->post('/ajukan', $this->payload($room))->assertSessionDoesntHaveErrors();

        $this->assertDatabaseCount('bookings', 3);
    }

    public function test_same_time_in_another_room_is_allowed(): void
    {
        $roomA = Room::factory()->create();
        $roomB = Room::factory()->create();
        Booking::factory()->approved()->create(['room_id' => $roomA->id]);

        $this->actingAs(User::factory()->create())
            ->post('/ajukan', $this->payload($roomB))->assertSessionDoesntHaveErrors();
    }

    public function test_validation_rules(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        // tanggal lewat
        $this->actingAs($user)->post('/ajukan', $this->payload($room, ['booking_date' => now()->subDay()->toDateString()]))
            ->assertSessionHasErrors('booking_date');
        // selesai sebelum mulai
        $this->post('/ajukan', $this->payload($room, ['start' => '11:00', 'end' => '09:00']))->assertSessionHasErrors('end');
        // di luar jam operasional
        $this->post('/ajukan', $this->payload($room, ['start' => '05:00']))->assertSessionHasErrors('start');
        // melebihi durasi maksimum (07:00-17:00 = 10 jam)
        $this->post('/ajukan', $this->payload($room, ['start' => '07:00', 'end' => '17:00']))->assertSessionHasErrors('end');
        // keperluan kosong
        $this->post('/ajukan', $this->payload($room, ['purpose' => '']))->assertSessionHasErrors('purpose');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_inactive_room_cannot_be_booked(): void
    {
        $room = Room::factory()->inactive()->create();

        $this->actingAs(User::factory()->create())->post('/ajukan', $this->payload($room))->assertSessionHasErrors('slot');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_user_can_cancel_own_booking_but_not_others(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->post(route('booking.cancel', $booking))->assertForbidden();
        $this->assertSame('pending', $booking->fresh()->status);

        $this->actingAs($owner)->post(route('booking.cancel', $booking))->assertSessionHas('ok');
        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_past_booking_cannot_be_cancelled(): void
    {
        $owner = User::factory()->create();
        $booking = Booking::factory()->approved()->create(['user_id' => $owner->id, 'booking_date' => now()->subDays(2)->toDateString()]);

        $this->actingAs($owner)->post(route('booking.cancel', $booking))->assertForbidden();
    }

    public function test_user_cannot_access_admin_area(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertRedirect(route('home'));
        $this->actingAs(User::factory()->create())->get('/admin/ruangan')->assertRedirect(route('home'));
    }

    public function test_admin_cannot_use_user_booking_form(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/ajukan')->assertRedirect(route('home'));
    }

    public function test_admin_can_approve_and_reject_once(): void
    {
        $admin = User::factory()->admin()->create();
        $a = Booking::factory()->create();
        $b = Booking::factory()->create();

        $this->actingAs($admin)->post(route('admin.bookings.approve', $a))->assertSessionHas('ok');
        $this->assertSame('approved', $a->fresh()->status);

        $this->post(route('admin.bookings.reject', $b), ['admin_note' => 'Bentrok kegiatan fakultas'])->assertSessionHas('ok');
        $this->assertSame('rejected', $b->fresh()->status);
        $this->assertSame('Bentrok kegiatan fakultas', $b->fresh()->admin_note);

        // sudah diproses -> tidak bisa diproses ulang
        $this->post(route('admin.bookings.reject', $a))->assertForbidden();
    }

    public function test_user_cannot_approve(): void
    {
        $booking = Booking::factory()->create();
        $this->actingAs(User::factory()->create())->post(route('admin.bookings.approve', $booking))->assertRedirect(route('home'));
        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_admin_room_crud_and_delete_protection(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/ruangan', ['name' => 'Ruang Baru', 'capacity' => 25, 'facilities' => 'AC', 'active' => 1])
            ->assertRedirect(route('admin.rooms.index'));
        $room = Room::where('name', 'Ruang Baru')->firstOrFail();

        $this->put("/admin/ruangan/{$room->id}", ['name' => 'Ruang Baru B', 'capacity' => 30])->assertRedirect();
        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'name' => 'Ruang Baru B', 'capacity' => 30, 'active' => 0]);

        $this->post("/admin/ruangan/{$room->id}/toggle");
        $this->assertTrue($room->fresh()->active);

        Booking::factory()->create(['room_id' => $room->id]);
        $this->delete("/admin/ruangan/{$room->id}")->assertSessionHas('err');
        $this->assertDatabaseHas('rooms', ['id' => $room->id]);

        $empty = Room::factory()->create();
        $this->delete("/admin/ruangan/{$empty->id}")->assertSessionHas('ok');
        $this->assertDatabaseMissing('rooms', ['id' => $empty->id]);
    }

    public function test_admin_csv_export(): void
    {
        $admin = User::factory()->admin()->create();
        Booking::factory()->create(['purpose' => 'Seminar uji ekspor']);

        $res = $this->actingAs($admin)->get('/admin/pengajuan/export');
        $res->assertOk();
        $this->assertStringContainsString('Seminar uji ekspor', $res->streamedContent());
    }
}
