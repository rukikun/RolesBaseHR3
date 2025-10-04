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
        // Drop table if it exists to recreate with proper structure
        Schema::dropIfExists('ai_generated_timesheets');
        
        Schema::create('ai_generated_timesheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('employee_name');
            $table->string('department')->nullable();
            $table->date('week_start_date');
            $table->json('weekly_data')->nullable(); // Store the weekly timesheet data
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->json('ai_insights')->nullable(); // Store AI insights
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('generated_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['employee_id', 'week_start_date']);
            $table->index(['status', 'generated_at']);
            $table->index('week_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generated_timesheets');
    }
};
