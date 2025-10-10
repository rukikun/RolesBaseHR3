<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Services\PHPMailerService;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email} {--otp : Send OTP test instead of basic email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $sendOtp = $this->option('otp');

        $this->info('Testing email configuration...');
        $this->info('Mail Driver: ' . config('mail.default'));
        $this->info('SMTP Host: ' . config('mail.mailers.smtp.host'));
        $this->info('SMTP Port: ' . config('mail.mailers.smtp.port'));
        $this->info('From Address: ' . config('mail.from.address'));
        $this->newLine();

        try {
            $phpMailer = new PHPMailerService();
            
            if ($sendOtp) {
                $this->info('Sending OTP test email using PHPMailer...');
                $result = $phpMailer->sendOtpEmail($email, '123456', 'Test User');
            } else {
                $this->info('Sending basic test email using PHPMailer...');
                $result = $phpMailer->sendTestEmail($email, 'HR3 System - PHPMailer Test', 'This is a test email using PHPMailer. If you receive this, your PHPMailer configuration is working!');
            }

            if ($result['success']) {
                $this->info('✅ ' . $result['message'] . ' to: ' . $email);
                $this->newLine();
                $this->info('Please check your email inbox (and spam folder) for the test message.');
                return Command::SUCCESS;
            } else {
                throw new \Exception($result['message']);
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Common solutions:');
            $this->line('1. Update your .env file with Gmail credentials');
            $this->line('2. Use Gmail App Password (not regular password)');
            $this->line('3. Enable 2-Step Verification on Gmail first');
            $this->line('4. Check SMTP settings: smtp.gmail.com:587 with TLS');
            $this->line('5. Make sure MAIL_USERNAME and MAIL_PASSWORD are set');
            
            return Command::FAILURE;
        }
    }
}
