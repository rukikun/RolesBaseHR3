<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OtpVerification;

class CleanupExpiredOtps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired OTP verification codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deletedCount = OtpVerification::cleanupExpired();
        
        $this->info("Cleaned up {$deletedCount} expired OTP records.");
        
        return Command::SUCCESS;
    }
}
