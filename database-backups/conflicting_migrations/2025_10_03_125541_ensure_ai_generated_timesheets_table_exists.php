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
        if (!Schema::hasTable('ai_generated_timesheets')) {
            Schema::create('ai_generated_timesheets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->date('week_start_date');
                $table->json('weekly_data');
                $table->decimal('total_hours', 8, 2)->default(0);
                $table->decimal('overtime_hours', 8, 2)->default(0);
                $table->json('ai_insights')->nullable();
                $table->enum('status', ['generated', 'approved', 'rejected'])->default('generated');
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();

                // Indexes
                $table->index(['employee_id', 'week_start_date']);
                $table->index('status');
                
                // Foreign key constraint
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                
                // Unique constraint to prevent duplicate AI timesheets for same employee/week
                $table->unique(['employee_id', 'week_start_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generated_timesheets');
    }
};
