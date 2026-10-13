<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@ruangku.test'], [
            'name' => 'Admin Fasilitas',
            'password' => 'Admin123!',
            'role' => User::ROLE_ADMIN,
            'organisasi' => 'Bagian Sarana Prasarana',
        ]);

        $user = User::updateOrCreate(['email' => 'user@ruangku.test'], [
            'name' => 'Mahasiswa Demo',
            'password' => 'User123!',
            'role' => User::ROLE_USER,
            'organisasi' => 'HIMA Informatika',
        ]);

        $rooms = collect([
            ['Aula Utama', 300, 'Sound system, proyektor, AC'],
            ['Lab Komputer 1', 40, '40 PC, AC, proyektor'],
            ['Ruang Seminar FMIPA', 80, 'Proyektor, whiteboard, AC'],
            ['Lapangan Futsal', 20, 'Lampu malam, ruang ganti'],
            ['Ruang Rapat Senat', 25, 'Meja rapat, TV layar lebar, AC'],
            ['Laboratorium Fisika', 30, 'Peralatan praktikum, AC'],
        ])->map(fn ($r) => Room::updateOrCreate(['name' => $r[0]], ['capacity' => $r[1], 'facilities' => $r[2], 'active' => true]));

        // Contoh data supaya kalender tidak kosong saat demo (tanpa duplikasi jika seeder dijalankan ulang)
        if (Booking::count() === 0) {
            $today = today()->toDateString();
            $tomorrow = today()->addDay()->toDateString();

            Booking::create(['user_id' => $user->id, 'room_id' => $rooms[0]->id, 'booking_date' => $today, 'start_time' => '09:00:00', 'end_time' => '12:00:00', 'purpose' => 'Seminar Nasional Informatika', 'status' => Booking::APPROVED]);
            Booking::create(['user_id' => $user->id, 'room_id' => $rooms[1]->id, 'booking_date' => $today, 'start_time' => '13:00:00', 'end_time' => '15:00:00', 'purpose' => 'Pelatihan Laravel untuk anggota HIMA', 'status' => Booking::APPROVED]);
            Booking::create(['user_id' => $user->id, 'room_id' => $rooms[3]->id, 'booking_date' => $tomorrow, 'start_time' => '16:00:00', 'end_time' => '18:00:00', 'purpose' => 'Turnamen futsal antar angkatan', 'status' => Booking::PENDING]);
            Booking::create(['user_id' => $user->id, 'room_id' => $rooms[2]->id, 'booking_date' => $tomorrow, 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'purpose' => 'Seminar proposal skripsi', 'status' => Booking::PENDING]);
        }
    }
}
