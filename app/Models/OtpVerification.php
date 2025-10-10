<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpVerification extends Model
{
    use HasFactory;

    protected $table = 'otp_verifications';

    protected $fillable = [
        'email',
        'otp_code',
        'expires_at',
        'is_used',
        'attempts',
        'max_attempts'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean'
    ];

    /**
     * Generate a new OTP for the given email
     */
    public static function generateOtp($email)
    {
        // Invalidate any existing OTPs for this email
        self::where('email', $email)->update(['is_used' => true]);

        // Generate 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create new OTP record
        return self::create([
            'email' => $email,
            'otp_code' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(10), // OTP expires in 10 minutes
            'is_used' => false,
            'attempts' => 0,
            'max_attempts' => 3
        ]);
    }

    /**
     * Verify OTP code
     */
    public static function verifyOtp($email, $otpCode)
    {
        $otpRecord = self::where('email', $email)
            ->where('otp_code', $otpCode)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            // Increment attempts for any non-used OTP for this email
            self::where('email', $email)
                ->where('is_used', false)
                ->increment('attempts');
            
            return false;
        }

        // Mark OTP as used
        $otpRecord->update(['is_used' => true]);
        
        return true;
    }

    /**
     * Check if user has exceeded max attempts
     */
    public static function hasExceededAttempts($email)
    {
        $otpRecord = self::where('email', $email)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        return $otpRecord && $otpRecord->attempts >= $otpRecord->max_attempts;
    }

    /**
     * Clean up expired OTPs
     */
    public static function cleanupExpired()
    {
        return self::where('expires_at', '<', Carbon::now())->delete();
    }
}
