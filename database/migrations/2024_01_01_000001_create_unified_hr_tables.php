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
        // Employees table (if not exists)
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('position');
                $table->string('department');
                $table->date('hire_date');
                $table->decimal('salary', 10, 2)->nullable();
                $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
                $table->enum('online_status', ['online', 'offline'])->default('offline');
                $table->timestamp('last_activity')->nullable();
                $table->timestamps();
                
                $table->index(['status', 'department']);
                $table->index('online_status');
            });
        }

        // Time entries table (if not exists)
        if (!Schema::hasTable('time_entries')) {
            Schema::create('time_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->date('work_date');
                $table->decimal('hours_worked', 5, 2)->default(0);
                $table->decimal('overtime_hours', 5, 2)->default(0);
                $table->text('description')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamp('clock_in')->nullable();
                $table->timestamp('clock_out')->nullable();
                $table->timestamps();
                
                $table->index(['employee_id', 'work_date']);
                $table->index('status');
            });
        }

        // Shift types table (if not exists)
        if (!Schema::hasTable('shift_types')) {
            Schema::create('shift_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->time('start_time');
                $table->time('end_time');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Shifts table (if not exists)
        if (!Schema::hasTable('shifts')) {
            Schema::create('shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('shift_type_id')->constrained('shift_types')->onDelete('cascade');
                $table->date('shift_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['employee_id', 'shift_date']);
                $table->index('status');
            });
        }

        // Leave types table (if not exists)
        if (!Schema::hasTable('leave_types')) {
            Schema::create('leave_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('max_days_per_year')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Leave requests table (if not exists)
        if (!Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
                $table->date('start_date');
                $table->date('end_date');
                $table->text('reason')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->timestamps();
                
                $table->index(['employee_id', 'status']);
                $table->index('start_date');
            });
        }

        // Leave balances table (if not exists)
        if (!Schema::hasTable('leave_balances')) {
            Schema::create('leave_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
                $table->integer('allocated_days')->default(0);
                $table->integer('used_days')->default(0);
                $table->integer('year');
                $table->timestamps();
                
                $table->unique(['employee_id', 'leave_type_id', 'year']);
            });
        }

        // Claim types table (if not exists)
        if (!Schema::hasTable('claim_types')) {
            Schema::create('claim_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('max_amount', 10, 2)->nullable();
                $table->boolean('requires_receipt')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Claims table (if not exists)
        if (!Schema::hasTable('claims')) {
            Schema::create('claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('claim_type_id')->constrained('claim_types')->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->date('claim_date');
                $table->text('description');
                $table->string('receipt_path')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->timestamps();
                
                $table->index(['employee_id', 'status']);
                $table->index('claim_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
        Schema::dropIfExists('claim_types');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('shift_types');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('employees');
    }
};
