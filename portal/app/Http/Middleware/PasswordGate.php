<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PasswordGate
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('portal_authed')) {
            return $next($request);
        }

        return redirect()->route('gate');
    }
}
