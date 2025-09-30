<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
            $table->index(['employee_id', 'work_date'], 'idx_employee_date');
            $table->index('work_date', 'idx_work_date');
            $table->index('status', 'idx_status');
            $table->index(['work_date', 'status'], 'idx_time_entries_date_status');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
