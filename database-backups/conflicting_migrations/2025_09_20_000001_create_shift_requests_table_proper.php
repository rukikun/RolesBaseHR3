<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing table if it exists to start fresh
        Schema::dropIfExists('shift_requests');
        
        Schema::create('shift_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('shift_type_id');
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('hours', 4, 2);
            $table->string('location', 255)->default('Main Office');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('employee_id');
            $table->index('shift_type_id');
            $table->index('status');
            $table->index('shift_date');
            
            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('shift_type_id')->references('id')->on('shift_types')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
        });

        // Insert sample data for testing
        DB::table('shift_requests')->insert([
            [
                'employee_id' => 42, // Mike Johnson
                'shift_type_id' => 1,
                'shift_date' => now()->addDays(2)->format('Y-m-d'),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'hours' => 8.00,
                'location' => 'Main Office',
                'notes' => 'Need to switch to day shift due to childcare arrangements',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 43, // John Kaizer
                'shift_type_id' => 2,
                'shift_date' => now()->addDays(3)->format('Y-m-d'),
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'hours' => 8.00,
                'location' => 'Main Office',
                'notes' => 'Doctor appointment - need evening shift coverage',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 42,
                'shift_type_id' => 1,
                'shift_date' => now()->addDays(5)->format('Y-m-d'),
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'hours' => 8.00,
                'location' => 'Main Office',
                'notes' => 'Willing to work night shift for project deadline',
                'status' => 'approved',
                'approved_by' => 42,
                'approved_at' => now(),
                'created_at' => now()->subDay(),
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
