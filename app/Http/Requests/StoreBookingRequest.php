<?php

namespace App\Http\Requests;

use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $slots = AvailabilityService::slots();

        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today',
                'before_or_equal:' . now()->addDays((int) config('ruangku.max_days_ahead'))->toDateString()],
            'start' => ['required', 'in:' . implode(',', $slots)],
            'end' => ['required', 'in:' . implode(',', $slots)],
            'purpose' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'Pilih ruangan.',
            'room_id.exists' => 'Ruangan tidak ditemukan.',
            'booking_date.required' => 'Tanggal wajib diisi.',
            'booking_date.date_format' => 'Format tanggal tidak valid.',
            'booking_date.after_or_equal' => 'Tanggal tidak boleh sudah lewat.',
            'booking_date.before_or_equal' => 'Tanggal terlalu jauh ke depan.',
            'start.required' => 'Jam mulai wajib diisi.',
            'start.in' => 'Jam mulai di luar jam operasional.',
            'end.required' => 'Jam selesai wajib diisi.',
            'end.in' => 'Jam selesai di luar jam operasional.',
            'purpose.required' => 'Isi keperluan peminjaman.',
            'purpose.min' => 'Keperluan minimal 5 karakter.',
            'purpose.max' => 'Keperluan maksimal 255 karakter.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $v) {
            if ($v->errors()->any()) {
                return;
            }

            $start = AvailabilityService::minutes($this->input('start'));
            $end = AvailabilityService::minutes($this->input('end'));

            if ($end <= $start) {
                $v->errors()->add('end', 'Jam selesai harus setelah jam mulai.');

                return;
            }
            if (($end - $start) > config('ruangku.max_hours') * 60) {
                $v->errors()->add('end', 'Durasi maksimal ' . config('ruangku.max_hours') . ' jam per pengajuan.');

                return;
            }

            $startAt = Carbon::createFromFormat('Y-m-d H:i', $this->input('booking_date') . ' ' . $this->input('start'));
            if ($startAt->isPast()) {
                $v->errors()->add('start', 'Jam mulai sudah lewat.');
            }
        }];
    }
}
