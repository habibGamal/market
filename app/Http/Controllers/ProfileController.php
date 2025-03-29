<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\BusinessType;
use App\Models\Gov;
use App\Services\OtpService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Display the support page.
     */
    public function support(Request $request): Response
    {
        $supportSettings = [
            'phone' => settings()->get('support_phone', '01234567890'),
            'email' => settings()->get('support_email', 'support@domain.com'),
            'address' => settings()->get('support_address', 'المبنى 123، شارع الرئيسي، المدينة'),
            'hours' => settings()->get('support_hours', 'من الأحد إلى الخميس، 9 صباحاً - 5 مساءً'),
            'chatHours' => settings()->get('support_chat_hours', 'متاحة من 9 صباحاً - 9 مساءً'),
        ];

        $policies = [
            'privacy' => settings()->get('privacy_policy', ''),
            'shipping' => settings()->get('shipping_policy', ''),
            'return' => settings()->get('return_policy', ''),
            'payment' => settings()->get('payment_policy', ''),
        ];

        return Inertia::render('Support/Index', [
            'supportSettings' => $supportSettings,
            'policies' => $policies,
        ]);
    }

    /**
     * Display the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'status' => session('status'),
        ]);
    }

    /**
     * Display the personal information edit page.
     */
    public function editPersonalInfo(Request $request): Response
    {
        return Inertia::render('Profile/PersonalInfo', [
            'businessTypes' => BusinessType::select('id', 'name')->get(),
        ]);
    }

    /**
     * Display the password change page with OTP verification.
     */
    public function editPassword(Request $request): Response
    {
        return Inertia::render('Profile/ChangePassword', [
            'phone' => auth()->guard('customer')->user()->phone,
        ]);
    }

    /**
     * Display the address edit page with OTP verification.
     */
    public function editAddress(Request $request): Response
    {
        return Inertia::render('Profile/AddressInfo', [
            'phone' => auth()->guard('customer')->user()->phone,
            'govs' => Gov::with('cities.areas')->get(),
        ]);
    }

    /**
     * Send OTP to customer phone for verification.
     */
    public function sendOtp(Request $request)
    {
        $phone = auth()->guard('customer')->user()->phone;
        $otp = $this->otpService->generateOtp($phone);
        $this->otpService->sendOtp($phone);

        if (config('app.real_otp', false) === false) {
            return response()->json(['otp' => $otp]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Update the user's personal information.
     */
    public function updatePersonalInfo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'nullable|email|unique:customers,email,' . auth()->guard('customer')->id(),
            'whatsapp' => 'nullable|string|max:255',
            'business_type_id' => 'required|exists:business_types,id',
        ]);

        auth()->guard('customer')->user()->update($validated);

        return Redirect::route('profile.edit')->with('message', 'تم تحديث المعلومات الشخصية بنجاح');
    }

    /**
     * Update the user's password with OTP verification.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8',
        ]);

        $customer = auth()->guard('customer')->user();
        $phone = $customer->phone;

        if (!$this->otpService->verifyOtp($phone, $validated['otp'])) {
            return back()->withErrors(['otp' => 'رمز التحقق غير صحيح أو منتهي الصلاحية']);
        }

        $customer->update([
            'password' => $validated['password']
        ]);

        return Redirect::route('profile.edit')->with('message', 'تم تغيير كلمة المرور بنجاح');
    }

    /**
     * Update the user's address with OTP verification.
     */
    public function updateAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => 'required|string|size:6',
            'gov_id' => 'required|exists:govs,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'village' => 'nullable|string',
            'location' => 'nullable|string',
            'address' => 'required|string|min:10',
        ]);

        $customer = auth()->guard('customer')->user();
        $phone = $customer->phone;

        if (!$this->otpService->verifyOtp($phone, $validated['otp'])) {
            return back()->withErrors(['otp' => 'رمز التحقق غير صحيح أو منتهي الصلاحية']);
        }

        $customer->update([
            'gov_id' => $validated['gov_id'],
            'city_id' => $validated['city_id'],
            'area_id' => $validated['area_id'],
            'village' => $validated['village'],
            'location' => $validated['location'],
            'address' => $validated['address'],
        ]);

        return Redirect::route('profile.edit')->with('message', 'تم تحديث معلومات العنوان بنجاح');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
