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
        Schema::create('monthly_timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('employee_name');
            $table->string('department')->nullable();
            $table->date('month_start_date');
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->unsignedInteger('timesheet_count')->default(0);
            $table->json('source_timesheet_ids')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'month_start_date']);
            $table->unique(['employee_id', 'month_start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_timesheets');
    }
};
