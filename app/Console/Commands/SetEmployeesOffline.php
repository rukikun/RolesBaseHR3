<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use Carbon\Carbon;

class SetEmployeesOffline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:set-offline {--minutes=30 : Minutes of inactivity before setting offline}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set employees offline if they have been inactive for specified minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = $this->option('minutes');
        $cutoffTime = Carbon::now()->subMinutes($minutes);

        $updatedCount = Employee::where('online_status', 'online')
            ->where(function($query) use ($cutoffTime) {
                $query->where('last_activity', '<', $cutoffTime)
                      ->orWhereNull('last_activity');
            })
            ->update([
                'online_status' => 'offline',
                'last_activity' => now()
            ]);

        $this->info("Set {$updatedCount} employees offline due to inactivity.");
        
        return 0;
    }
}
