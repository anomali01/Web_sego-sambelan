<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotBuyer
{
    /**
     * Redirect admin and driver users away from buyer-only pages
     * (menu, cart, checkout, order tracking) to their respective dashboards.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isAdmin() || $user->isSeller()) {
                return redirect('/admin/dashboard')
                    ->with('warning', 'Admin/Seller tidak dapat mengakses halaman pemesanan.');
            }

            if ($user->isDriver()) {
                return redirect()->route('driver.orders.index')
                    ->with('warning', 'Driver tidak dapat mengakses halaman pemesanan.');
            }
        }

        return $next($request);
    }
}
