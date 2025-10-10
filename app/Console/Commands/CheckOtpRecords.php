<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OtpVerification;

class CheckOtpRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:check {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check OTP records in database for a specific email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if ($email) {
            $this->info("Checking OTP records for: {$email}");
            $otps = OtpVerification::where('email', $email)->orderBy('created_at', 'desc')->get();
        } else {
            $this->info("Checking all OTP records:");
            $otps = OtpVerification::orderBy('created_at', 'desc')->take(10)->get();
        }

        if ($otps->count() === 0) {
            $this->warn('No OTP records found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$otps->count()} OTP record(s):");
        $this->newLine();

        $headers = ['Email', 'OTP Code', 'Created', 'Expires', 'Used', 'Attempts'];
        $rows = [];

        foreach ($otps as $otp) {
            $rows[] = [
                $otp->email,
                $otp->otp_code,
                $otp->created_at->format('Y-m-d H:i:s'),
                $otp->expires_at->format('Y-m-d H:i:s'),
                $otp->is_used ? 'Yes' : 'No',
                "{$otp->attempts}/{$otp->max_attempts}"
            ];
        }

        $this->table($headers, $rows);

        return Command::SUCCESS;
    }
}
