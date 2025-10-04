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
        Schema::create('employee_timesheet_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('week_start_date');
            $table->date('week_end_date');
            
            // Monday
            $table->date('monday_date')->nullable();
            $table->string('monday_time_in')->nullable();
            $table->string('monday_break')->nullable();
            $table->string('monday_time_out')->nullable();
            $table->decimal('monday_total_hours', 5, 2)->default(0);
            $table->decimal('monday_actual_time', 5, 2)->default(0);
            
            // Tuesday
            $table->date('tuesday_date')->nullable();
            $table->string('tuesday_time_in')->nullable();
            $table->string('tuesday_break')->nullable();
            $table->string('tuesday_time_out')->nullable();
            $table->decimal('tuesday_total_hours', 5, 2)->default(0);
            $table->decimal('tuesday_actual_time', 5, 2)->default(0);
            
            // Wednesday
            $table->date('wednesday_date')->nullable();
            $table->string('wednesday_time_in')->nullable();
            $table->string('wednesday_break')->nullable();
            $table->string('wednesday_time_out')->nullable();
            $table->decimal('wednesday_total_hours', 5, 2)->default(0);
            $table->decimal('wednesday_actual_time', 5, 2)->default(0);
            
            // Thursday
            $table->date('thursday_date')->nullable();
            $table->string('thursday_time_in')->nullable();
            $table->string('thursday_break')->nullable();
            $table->string('thursday_time_out')->nullable();
            $table->decimal('thursday_total_hours', 5, 2)->default(0);
            $table->decimal('thursday_actual_time', 5, 2)->default(0);
            
            // Friday
            $table->date('friday_date')->nullable();
            $table->string('friday_time_in')->nullable();
            $table->string('friday_break')->nullable();
            $table->string('friday_time_out')->nullable();
            $table->decimal('friday_total_hours', 5, 2)->default(0);
            $table->decimal('friday_actual_time', 5, 2)->default(0);
            
            // Saturday (optional)
            $table->date('saturday_date')->nullable();
            $table->string('saturday_time_in')->nullable();
            $table->string('saturday_break')->nullable();
            $table->string('saturday_time_out')->nullable();
            $table->decimal('saturday_total_hours', 5, 2)->default(0);
            $table->decimal('saturday_actual_time', 5, 2)->default(0);
            
            // Sunday (optional)
            $table->date('sunday_date')->nullable();
            $table->string('sunday_time_in')->nullable();
            $table->string('sunday_break')->nullable();
            $table->string('sunday_time_out')->nullable();
            $table->decimal('sunday_total_hours', 5, 2)->default(0);
            $table->decimal('sunday_actual_time', 5, 2)->default(0);
            
            // Summary fields
            $table->decimal('total_week_hours', 6, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('supervisor_id')->references('id')->on('employees')->onDelete('set null');
            
            // Indexes
            $table->index(['employee_id', 'week_start_date']);
            $table->index('status');
            $table->unique(['employee_id', 'week_start_date'], 'unique_employee_week');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_timesheet_details');
    }
};
