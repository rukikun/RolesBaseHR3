<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shift_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('shift_type_id');
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('hours', 4, 2);
            $table->string('location');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('status');
            $table->index('shift_date');
            
            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('shift_type_id')->references('id')->on('shift_types')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
        });

        // Insert sample data
        DB::table('shift_requests')->insert([
            [
                'employee_id' => 1,
                'shift_type_id' => 2,
                'shift_date' => '2025-09-15',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'hours' => 8.00,
                'location' => 'Main Office',
                'notes' => 'Need to switch to day shift due to childcare arrangements',
                'status' => 'pending',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'employee_id' => 2,
                'shift_type_id' => 1,
                'shift_date' => '2025-09-18',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'hours' => 8.00,
                'location' => 'Main Office',
                'notes' => 'Doctor appointment - need evening shift coverage',
                'status' => 'pending',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'employee_id' => 3,
                'shift_type_id' => 1,
                'shift_date' => '2025-09-20',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'hours' => 8.00,
                'location' => 'Main Office',
                'notes' => 'Willing to work night shift for project deadline',
                'status' => 'pending',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'employee_id' => 4,
                'shift_type_id' => 2,
                'shift_date' => '2025-09-22',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'hours' => 8.00,
                'location' => 'Main Office',
                'notes' => 'Want to swap weekend shift with weekday shift',
                'status' => 'pending',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'employee_id' => 5,
                'shift_type_id' => 3,
                'shift_date' => '2025-09-25',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'hours' => 8.00,
                'location' => 'Main Office',
                'notes' => 'Prefer early morning shift for better work-life balance',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_requests');
    }
};
