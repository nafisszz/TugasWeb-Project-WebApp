<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/ketersediaan', [AvailabilityController::class, 'index'])->name('availability');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

    // Pengguna (mahasiswa / organisasi)
    Route::middleware(EnsureRole::class . ':user')->group(function () {
        Route::get('/beranda', [BookingController::class, 'dashboard'])->name('user.dashboard');
        Route::get('/ajukan', [BookingController::class, 'create'])->name('booking.create');
        Route::post('/ajukan', [BookingController::class, 'store'])->name('booking.store');
    });

    // Pembatalan: pemilik atau admin (dicek oleh BookingPolicy)
    Route::post('/pengajuan/{booking}/batal', [BookingController::class, 'cancel'])->name('booking.cancel');

    // Admin sarana prasarana
    Route::prefix('admin')->name('admin.')->middleware(EnsureRole::class . ':admin')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::get('/pengajuan', [Admin\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/pengajuan/export', [Admin\BookingController::class, 'export'])->name('bookings.export');
        Route::post('/pengajuan/{booking}/setujui', [Admin\BookingController::class, 'approve'])->name('bookings.approve');
        Route::post('/pengajuan/{booking}/tolak', [Admin\BookingController::class, 'reject'])->name('bookings.reject');
        Route::post('/pengajuan/{booking}/batal', [Admin\BookingController::class, 'cancel'])->name('bookings.cancel');

        Route::get('/ruangan', [Admin\RoomController::class, 'index'])->name('rooms.index');
        Route::get('/ruangan/tambah', [Admin\RoomController::class, 'create'])->name('rooms.create');
        Route::post('/ruangan', [Admin\RoomController::class, 'store'])->name('rooms.store');
        Route::get('/ruangan/{room}/ubah', [Admin\RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/ruangan/{room}', [Admin\RoomController::class, 'update'])->name('rooms.update');
        Route::post('/ruangan/{room}/toggle', [Admin\RoomController::class, 'toggle'])->name('rooms.toggle');
        Route::delete('/ruangan/{room}', [Admin\RoomController::class, 'destroy'])->name('rooms.destroy');
    });
});
