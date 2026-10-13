<?php

namespace App\Http\Controllers;

use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request, AvailabilityService $availability)
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);

        $date = Carbon::parse($request->input('date', today()->toDateString()))->toDateString();

        return view('availability', [
            'date' => $date,
            'prev' => Carbon::parse($date)->subDay()->toDateString(),
            'next' => Carbon::parse($date)->addDay()->toDateString(),
            'timeline' => $availability->timeline($date, $request->user()),
            'hours' => AvailabilityService::hours(),
        ]);
    }
}
