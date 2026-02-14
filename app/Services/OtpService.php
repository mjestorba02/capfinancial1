<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OtpService
{
    public const EXPIRY_MINUTES = 10;

    public const TYPE_ADMIN = 'admin';
    public const TYPE_EMPLOYEE = 'employee';

    /**
     * Create OTP, invalidate any previous for this user, send email, return the OtpVerification model.
     */
    public function createAndSend(string $type, int $verifiableId, string $email): OtpVerification
    {
        $this->invalidateExisting($type, $verifiableId);

        $code = Str::padLeft((string) random_int(0, 999999), 6, '0');
        $expiresAt = now()->addMinutes(self::EXPIRY_MINUTES);

        $otp = OtpVerification::create([
            'type' => $type,
            'verifiable_id' => $verifiableId,
            'email' => $email,
            'otp_code' => $code,
            'expires_at' => $expiresAt,
        ]);

        Mail::to($email)->send(new OtpMail($code, $type));

        return $otp;
    }

    public function invalidateExisting(string $type, int $verifiableId): void
    {
        OtpVerification::where('type', $type)
            ->where('verifiable_id', $verifiableId)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    /**
     * Verify OTP. Returns true if valid and marks as used.
     */
    public function verify(string $type, int $verifiableId, string $code): bool
    {
        $otp = OtpVerification::where('type', $type)
            ->where('verifiable_id', $verifiableId)
            ->where('otp_code', $code)
            ->first();

        if (!$otp || !$otp->isValid()) {
            return false;
        }

        $otp->update(['used_at' => now()]);
        return true;
    }
}
