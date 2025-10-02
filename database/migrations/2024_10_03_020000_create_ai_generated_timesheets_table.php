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
        Schema::create('ai_generated_timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('week_start_date');
            $table->json('weekly_data'); // Store the complete weekly timesheet data
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->json('ai_insights')->nullable(); // Store AI analysis
            $table->enum('status', ['generated', 'approved', 'rejected'])->default('generated');
            $table->timestamp('generated_at');
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Ensure one AI timesheet per employee per week
            $table->unique(['employee_id', 'week_start_date']);
            
            // Indexes for performance
            $table->index(['employee_id', 'status']);
            $table->index(['week_start_date', 'status']);
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
