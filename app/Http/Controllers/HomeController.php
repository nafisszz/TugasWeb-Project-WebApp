<?php

namespace App\Http\Controllers;

use App\Services\AvailabilityService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, AvailabilityService $availability)
    {
        $user = $request->user();

        if ($user) {
            $request->session()->reflash(); // pesan flash dari redirect sebelumnya tetap terbaca
            return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'user.dashboard');
        }

        $date = today()->toDateString();

        return view('welcome', [
            'date' => $date,
            'timeline' => $availability->timeline($date, null),
            'hours' => AvailabilityService::hours(),
        ]);
    }
}
