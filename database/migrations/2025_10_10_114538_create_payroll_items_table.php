<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('timesheet_id')->nullable(); // Reference to ai_generated_timesheets
            $table->unsignedBigInteger('employee_id');
            $table->string('employee_name');
            $table->string('department')->nullable();
            $table->string('week_period');
            $table->date('week_start_date');
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('regular_rate', 8, 2)->default(500.00); // PHP 500 per hour
            $table->decimal('overtime_rate', 8, 2)->default(750.00); // PHP 750 per hour (1.5x)
            $table->decimal('regular_amount', 10, 2)->default(0);
            $table->decimal('overtime_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'processed', 'paid'])->default('pending');
            $table->timestamp('processed_date')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->json('timesheet_data')->nullable(); // Store original timesheet data
            $table->timestamps();
            
            $table->index(['employee_id', 'week_start_date']);
            $table->index('status');
            $table->index('processed_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};