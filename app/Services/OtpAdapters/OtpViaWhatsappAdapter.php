<?php

namespace App\Services\OtpAdapters;

use App\Enums\SettingKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpViaWhatsappAdapter implements OtpSenderInterface
{
    /**
     * Send an OTP code to a phone number via WhatsApp
     *
     * @param string $phone The phone number to send the OTP to
     * @param string $otp The OTP code to send
     * @return bool True if the OTP was sent successfully, false otherwise
     */
    public function send(string $phone, string $otp): bool
    {
        try {
            $endpoint = settings()->get(SettingKey::WHATSAPP_SERVER_ENDPOINT);

            if (empty($endpoint)) {
                Log::error('WhatsApp server endpoint is not configured.');
                return false;
            }


            $response = Http::asForm()->post($endpoint, [
                'phone' => $this->formatPhoneNumber($phone),
                'code' => $otp,
            ]);

            if ($response->successful()) {
                Log::info('OTP sent successfully via WhatsApp', ['phone' => $phone]);
                return true;
            }

            Log::error('Failed to send OTP via WhatsApp', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception when sending OTP via WhatsApp', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Format the phone number to ensure it's in the right format for WhatsApp
     *
     * @param string $phone The phone number to format
     * @return string The formatted phone number
     */
    private function formatPhoneNumber(string $phone): string
    {


        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If the phone starts with '01', add '2' prefix to make it '201'
        if (substr($phone, 0, 2) === '01') {
            $phone = '2' . $phone;
        }

        return $phone;
    }
}
