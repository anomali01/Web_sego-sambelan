<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    /**
     * Buyer users must have a complete profile (address + phone) before
     * accessing any protected route. Admin/Seller bypass this check.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Not authenticated — let auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Admin/Seller bypass profile check
        if ($user->isSeller()) {
            return $next($request);
        }

        // Check if buyer has a complete profile
        if ($user->isBuyer() && !$user->hasCompleteProfile()) {
            return redirect('/profile/complete')
                ->with('warning', 'Silakan lengkapi data alamat Anda sebelum melanjutkan.');
        }

        return $next($request);
    }
}
