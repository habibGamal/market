<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class OtpService
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY = 300; // 5 minutes

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

    private function getCacheKey(string $phone): string
    {
        return "otp_{$phone}";
    }
}
