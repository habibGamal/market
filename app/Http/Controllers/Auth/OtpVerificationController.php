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
        $sent = $this->otpService->sendOtp($phone);

        // For development, we'll return the OTP in the response if REAL_OTP is disabled
        if (config('app.real_otp', false) === false) {
            return response()->json(['otp' => $otp, 'sent' => $sent]);
        }

        if ($sent) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'فشل في إرسال رمز التحقق'], 500);
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
