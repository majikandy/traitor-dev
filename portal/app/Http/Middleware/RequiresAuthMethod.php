<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * If the authenticated user has no password and no passkeys, they must set
 * one up before they can access anything. This guards against accounts in a
 * limbo state (e.g. passkey registration was interrupted mid-flow).
 */
class RequiresAuthMethod
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && !$user->has_password && $user->passkeys()->count() === 0) {
            if (!$request->routeIs('setup-auth', 'setup-auth.save', 'passkeys.register', 'passkeys.register-options', 'logout')) {
                return redirect()->route('setup-auth');
            }
        }

        return $next($request);
    }
}
