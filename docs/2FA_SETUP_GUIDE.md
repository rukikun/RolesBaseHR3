# 2FA (Two-Factor Authentication) Setup Guide

This guide will help you set up the Two-Factor Authentication system for the Jetlouge Travels Admin Portal.

## Overview

The 2FA system adds an extra layer of security to the admin login process by requiring users to verify their identity with a 6-digit code sent to their email after entering their credentials.

## Features

- **Secure Login Flow**: Users must verify both password and email-sent OTP
- **Email Integration**: OTP codes sent via Gmail SMTP
- **Modern UI**: Beautiful verification interface matching the admin portal design
- **Auto-Expiry**: OTP codes expire after 10 minutes
- **Rate Limiting**: Maximum 3 attempts per OTP code
- **Resend Functionality**: Users can request new codes with cooldown period
- **Session Management**: Secure session handling throughout the process

## Setup Instructions

### 1. Run Database Migration

First, create the OTP verification table:

```bash
php artisan migrate
```

This will create the `otp_verifications` table with the following structure:
- `id` - Primary key
- `email` - User email address
- `otp_code` - 6-digit verification code
- `expires_at` - Expiration timestamp
- `is_used` - Whether the code has been used
- `attempts` - Number of failed attempts
- `max_attempts` - Maximum allowed attempts (default: 3)
- `timestamps` - Created/updated timestamps

### 2. Configure Email Settings

#### Option A: Gmail SMTP (Recommended)

1. **Enable 2-Step Verification** on your Gmail account
2. **Generate App Password**:
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate password for "Mail"
3. **Update .env file**:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-gmail@gmail.com"
MAIL_FROM_NAME="Jetlouge Travels Admin"
```

#### Option B: Other SMTP Providers

For other email providers, update the SMTP settings accordingly:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@domain.com"
MAIL_FROM_NAME="Jetlouge Travels Admin"
```

### 3. Test Email Configuration

Test if emails are working:

```bash
php artisan tinker
```

Then run:

```php
Mail::raw('Test email from HR3 System', function ($message) {
    $message->to('test@example.com')->subject('Test Email');
});
```

### 4. Optional: Set up Automatic Cleanup

Add the OTP cleanup command to your scheduler in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Clean up expired OTPs every hour
    $schedule->command('otp:cleanup')->hourly();
}
```

Then set up the Laravel scheduler:

```bash
# Add to crontab (Linux/Mac)
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1

# Or run manually when needed
php artisan otp:cleanup
```

## How It Works

### Login Flow

1. **User enters credentials** on `/admin/login`
2. **System validates** email and password
3. **OTP generated** and sent to user's email
4. **User redirected** to `/admin/otp-verification`
5. **User enters 6-digit code** from email
6. **System verifies OTP** and completes login
7. **User redirected** to dashboard

### Security Features

- **Session-based verification**: OTP email stored in session
- **Time-limited codes**: 10-minute expiration
- **Attempt limiting**: Maximum 3 failed attempts
- **Automatic cleanup**: Expired codes removed from database
- **Secure logout**: Clears all session data

### Email Template

The system sends beautifully formatted HTML emails with:
- Jetlouge Travels branding
- Clear 6-digit OTP code
- Security warnings
- Professional design

## Troubleshooting

### Common Issues

1. **Emails not sending**:
   - Check SMTP credentials in `.env`
   - Verify Gmail app password is correct
   - Check Laravel logs: `storage/logs/laravel.log`

2. **OTP codes not working**:
   - Check system time is correct
   - Verify database migration ran successfully
   - Check for expired codes

3. **Session issues**:
   - Clear application cache: `php artisan cache:clear`
   - Clear config cache: `php artisan config:clear`

### Debug Commands

```bash
# Check mail configuration
php artisan config:show mail

# Test OTP generation
php artisan tinker
>>> App\Models\OtpVerification::generateOtp('test@example.com')

# Clean up expired OTPs
php artisan otp:cleanup

# Check database
php artisan tinker
>>> App\Models\OtpVerification::all()
```

## Security Considerations

- **Use HTTPS**: Always use SSL/TLS in production
- **Secure SMTP**: Use encrypted SMTP connections
- **Rate Limiting**: Consider adding rate limiting to prevent abuse
- **Monitoring**: Monitor failed login attempts
- **Backup Codes**: Consider implementing backup authentication methods

## Customization

### Modify OTP Length

To change from 6-digit to 4-digit codes, update `OtpVerification.php`:

```php
// Change this line in generateOtp method
$otpCode = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

// Update validation in AuthController
'otp_code' => ['required', 'string', 'size:4'],
```

### Modify Expiration Time

To change from 10 minutes to 5 minutes:

```php
// In OtpVerification.php generateOtp method
'expires_at' => Carbon::now()->addMinutes(5),
```

### Customize Email Template

Edit the email templates:
- HTML: `resources/views/emails/otp-verification.blade.php`
- Text: `resources/views/emails/otp-verification-text.blade.php`

## Files Created/Modified

### New Files
- `app/Models/OtpVerification.php` - OTP model
- `app/Mail/OtpMail.php` - Email class
- `app/Console/Commands/CleanupExpiredOtps.php` - Cleanup command
- `database/migrations/2025_10_10_020000_create_otp_verifications_table.php` - Migration
- `resources/views/otp_verification.blade.php` - OTP form
- `resources/views/emails/otp-verification.blade.php` - HTML email template
- `resources/views/emails/otp-verification-text.blade.php` - Text email template

### Modified Files
- `app/Http/Controllers/AuthController.php` - Added 2FA methods
- `routes/web.php` - Added OTP routes
- `config/mail.php` - Added encryption setting
- `.env.example` - Updated mail configuration

## Support

For issues or questions about the 2FA system:
1. Check the troubleshooting section above
2. Review Laravel logs for error details
3. Test email configuration separately
4. Verify database migration completed successfully

The 2FA system is now ready to use! Users will be prompted for email verification after entering their login credentials.
