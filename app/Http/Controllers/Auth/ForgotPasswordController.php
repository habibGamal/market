<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    public function __construct(private OtpService $otpService)
    {
    }

    public function create()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function store(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'exists:customers,phone'],
        ], [
            'phone.exists' => 'رقم الهاتف غير مسجل لدينا'
        ]);

        // Store phone in session for later use
        session(['reset_password_phone' => $request->phone]);

        return Inertia::render('Auth/PasswordResetOtp', [
            'phone' => $request->phone
        ]);
    }

    public function send(Request $request)
    {
        $phone = session('reset_password_phone');

        if (!$phone) {
            return response()->json(['message' => 'رقم الهاتف غير متوفر'], 400);
        }

        $otp = $this->otpService->generateOtp($phone);
        $this->otpService->sendOtp($phone);
        if (app()->environment('local')) {
            logger()->info("Password Reset OTP for {$phone}: {$otp}");
            return response()->json(['otp' => $otp]);
        }

        return response()->json(['message' => 'تم إرسال رمز التحقق']);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $phone = session('reset_password_phone');

        if (!$phone) {
            return back()->withErrors(['otp' => 'صلاحية إعادة تعيين كلمة المرور منتهية']);
        }

        if (!$this->otpService->verifyOtp($phone, $request->otp)) {
            return back()->withErrors(['otp' => 'رمز التحقق غير صحيح أو منتهي الصلاحية']);
        }

        // Update password
        $customer = Customer::where('phone', $phone)->first();
        $customer->update([
            'password' => $request->password
        ]);

        // Clear reset password session data
        session()->forget('reset_password_phone');

        return redirect()->route('login')->with('success', 'تم تغيير كلمة المرور بنجاح');
    }
}
