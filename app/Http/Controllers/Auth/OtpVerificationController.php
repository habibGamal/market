<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OtpVerificationController extends Controller
{
    public function __construct(private OtpService $otpService)
    {
    }

    public function create(Request $request)
    {
        return Inertia::render('Auth/OtpVerification', [
            'phone' => auth()->guard('customer')->user()->phone,
        ]);
    }

    public function sendOtp(Request $request)
    {
        $phone = auth()->guard('customer')->user()->phone;

        $otp = $this->otpService->generateOtp($phone);

        // Here you would typically send the OTP via SMS
        // For development, we'll just return it in the response
        if (config('app.debug')) {
            return response()->json(['otp' => $otp]);
        }

        return response()->noContent();
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $phone = auth()->guard('customer')->user()->phone;

        if (!$this->otpService->verifyOtp($phone, $validated['otp'])) {
            return back()->withErrors([
                'otp' => 'رمز التحقق غير صحيح'
            ]);
        }

        DB::transaction(function () use ($phone) {
            $customer = Customer::where('phone', $phone)->first();

            if ($customer) {
                $customer->phone_verified_at = now();
                $customer->save();
            }
        });

        return redirect()->intended('/');
    }
}
