<?php

namespace App\Services;

use App\Services\OtpAdapters\OtpSenderInterface;
use App\Services\OtpAdapters\OtpViaWhatsappAdapter;
use Illuminate\Support\Facades\Cache;

class OtpService
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY = 300; // 5 minutes
    private ?OtpSenderInterface $otpSender;

    public function __construct()
    {
        $this->otpSender = app()->make(OtpViaWhatsappAdapter::class);
    }

    public function generateOtp(string $phone): string
    {
        $otp = str_pad((string)random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);

        Cache::put($this->getCacheKey($phone), $otp, self::OTP_EXPIRY);

        return $otp;
    }

    public function verifyOtp(string $phone, string $otp): bool
    {
        $storedOtp = Cache::get($this->getCacheKey($phone));

        if ($storedOtp && $storedOtp === $otp) {
            Cache::forget($this->getCacheKey($phone));
            return true;
        }

        return false;
    }

    public function sendOtp(string $phone): bool
    {
        $otp = Cache::get($this->getCacheKey($phone));

        if (empty($otp)) {
            $otp = $this->generateOtp($phone);
        }

        if (config('app.real_otp', false) === false) {
            // In testing/development mode, just return true without sending
            return true;
        }

        return $this->otpSender->send($phone, $otp);
    }

    private function getCacheKey(string $phone): string
    {
        return "otp_{$phone}";
    }
}
