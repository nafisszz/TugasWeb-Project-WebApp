<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('purpose');
            $table->string('status', 12)->default('pending'); // pending|approved|rejected|cancelled
            $table->string('admin_note')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'booking_date', 'status']);
            $table->index(['user_id', 'booking_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
