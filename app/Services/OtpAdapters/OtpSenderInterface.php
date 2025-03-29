<?php

namespace App\Services\OtpAdapters;

interface OtpSenderInterface
{
    /**
     * Send an OTP code to a phone number
     *
     * @param string $phone The phone number to send the OTP to
     * @param string $otp The OTP code to send
     * @return bool True if the OTP was sent successfully, false otherwise
     */
    public function send(string $phone, string $otp): bool;
}
