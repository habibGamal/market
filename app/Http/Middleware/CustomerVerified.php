<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = auth()->guard('customer')->user();

        // if (!$customer || !$customer->phone_verified_at) {
        //     if ($request->expectsJson()) {
        //         return response()->json(['message' => 'يجب التحقق من رقم هاتفك أولاً'], 403);
        //     }

        //     return redirect()->route('otp.verify', ['phone' => $customer?->phone]);
        // }

        return $next($request);
    }
}
