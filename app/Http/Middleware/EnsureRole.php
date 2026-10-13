<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Pemakaian di route: ->middleware(EnsureRole::class.':admin') */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role !== $role) {
            return redirect()->route('home')->with('err', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
