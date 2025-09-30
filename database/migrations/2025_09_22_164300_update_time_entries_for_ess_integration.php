<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, ensure the time_entries table exists with proper structure
        if (!Schema::hasTable('time_entries')) {
            Schema::create('time_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->date('work_date');
                $table->time('clock_in_time')->nullable();
                $table->time('clock_out_time')->nullable();
                $table->decimal('hours_worked', 4, 2)->default(0.00);
                $table->decimal('overtime_hours', 4, 2)->default(0.00);
                $table->decimal('break_duration', 4, 2)->default(0.00);
                $table->text('description')->nullable();
                $table->text('notes')->nullable(); // Additional notes field
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
                
                // Indexes for performance
                $table->index(['employee_id', 'work_date'], 'idx_employee_date');
                $table->index('work_date', 'idx_work_date');
                $table->index('status', 'idx_status');
                $table->index(['work_date', 'status'], 'idx_time_entries_date_status');
            });
        } else {
            // If table exists, ensure it has the required columns
            Schema::table('time_entries', function (Blueprint $table) {
                if (!Schema::hasColumn('time_entries', 'clock_in_time')) {
                    $table->time('clock_in_time')->nullable()->after('work_date');
                }
                if (!Schema::hasColumn('time_entries', 'clock_out_time')) {
                    $table->time('clock_out_time')->nullable()->after('clock_in_time');
                }
                if (!Schema::hasColumn('time_entries', 'notes')) {
                    $table->text('notes')->nullable()->after('description');
                }
                if (!Schema::hasColumn('time_entries', 'break_duration')) {
                    $table->decimal('break_duration', 4, 2)->default(0.00)->after('overtime_hours');
                }
                if (!Schema::hasColumn('time_entries', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable()->after('status');
                }
                if (!Schema::hasColumn('time_entries', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
            });
        }

        // Create sample data for testing if table is empty
        $count = DB::table('time_entries')->count();
        if ($count === 0) {
            // Get some employee IDs for sample data
            $employeeIds = DB::table('employees')->pluck('id')->take(3)->toArray();
            
            if (!empty($employeeIds)) {
                $sampleData = [];
                $today = now();
                
                foreach ($employeeIds as $index => $employeeId) {
                    // Create entries for the last 5 days
                    for ($i = 0; $i < 5; $i++) {
                        $workDate = $today->copy()->subDays($i);
                        $clockIn = $workDate->copy()->setTime(9, 0, 0); // 9:00 AM
                        $clockOut = $workDate->copy()->setTime(17, 30, 0); // 5:30 PM
                        
                        // Add some variation
                        $clockIn->addMinutes(rand(-30, 30));
                        $clockOut->addMinutes(rand(-30, 60));
                        
                        $hoursWorked = $clockOut->diffInMinutes($clockIn) / 60;
                        $overtimeHours = max(0, $hoursWorked - 8);
                        $regularHours = min(8, $hoursWorked);
                        
                        $sampleData[] = [
                            'employee_id' => $employeeId,
                            'work_date' => $workDate->format('Y-m-d'),
                            'clock_in_time' => $clockIn->format('H:i:s'),
                            'clock_out_time' => $clockOut->format('H:i:s'),
                            'hours_worked' => round($regularHours, 2),
                            'overtime_hours' => round($overtimeHours, 2),
                            'break_duration' => 1.00, // 1 hour break
                            'description' => 'Regular work day - Employee ' . ($index + 1),
                            'notes' => 'Auto-generated from ESS clock-in/out system',
                            'status' => ['pending', 'approved', 'pending'][rand(0, 2)],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                
                DB::table('time_entries')->insert($sampleData);
            }
        }
    }

    public function down(): void
    {
        // Don't drop the table as it might contain important data
        // Just remove the columns we added if needed
        if (Schema::hasTable('time_entries')) {
            Schema::table('time_entries', function (Blueprint $table) {
                if (Schema::hasColumn('time_entries', 'notes')) {
                    $table->dropColumn('notes');
                }
                if (Schema::hasColumn('time_entries', 'break_duration')) {
                    $table->dropColumn('break_duration');
                }
            });
        }
    }
};
