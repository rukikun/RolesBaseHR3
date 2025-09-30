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
        // Create employees table if it doesn't exist
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('email')->unique();
                $table->string('phone', 20)->nullable();
                $table->string('position', 100);
                $table->string('department', 100);
                $table->date('hire_date');
                $table->decimal('salary', 10, 2)->default(0);
                $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
                $table->enum('online_status', ['online', 'offline'])->default('offline');
                $table->timestamp('last_activity')->nullable();
                $table->string('password')->nullable();
                $table->string('profile_picture')->nullable();
                $table->rememberToken();
                $table->timestamps();
                
                // Indexes for performance
                $table->index(['status']);
                $table->index(['department']);
                $table->index(['online_status']);
                $table->index(['last_activity']);
            });
        }

        // Create time_entries table if it doesn't exist
        if (!Schema::hasTable('time_entries')) {
            Schema::create('time_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->date('work_date');
                $table->time('clock_in_time')->nullable();
                $table->time('clock_out_time')->nullable();
                $table->decimal('hours_worked', 5, 2)->default(0);
                $table->decimal('overtime_hours', 5, 2)->default(0);
                $table->text('description')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamps();
                
                // Indexes for performance
                $table->index(['employee_id', 'work_date']);
                $table->index(['work_date']);
                $table->index(['status']);
                $table->unique(['employee_id', 'work_date']);
            });
        }

        // Create shifts table if it doesn't exist
        if (!Schema::hasTable('shifts')) {
            Schema::create('shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('shift_type_id')->nullable()->constrained('shift_types')->onDelete('set null');
                $table->date('shift_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                // Indexes for performance
                $table->index(['employee_id', 'shift_date']);
                $table->index(['shift_date']);
                $table->index(['status']);
            });
        }

        // Create shift_types table if it doesn't exist
        if (!Schema::hasTable('shift_types')) {
            Schema::create('shift_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->time('default_start_time');
                $table->time('default_end_time');
                $table->decimal('hourly_rate', 8, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['is_active']);
            });
        }

        // Create leave_requests table if it doesn't exist
        if (!Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('days_requested');
                $table->text('reason')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('manager_notes')->nullable();
                $table->timestamps();
                
                // Indexes for performance
                $table->index(['employee_id']);
                $table->index(['status']);
                $table->index(['start_date', 'end_date']);
            });
        }

        // Create leave_types table if it doesn't exist
        if (!Schema::hasTable('leave_types')) {
            Schema::create('leave_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->integer('max_days_per_year')->default(0);
                $table->boolean('requires_approval')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['is_active']);
            });
        }

        // Create claims table if it doesn't exist
        if (!Schema::hasTable('claims')) {
            Schema::create('claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('claim_type_id')->constrained('claim_types')->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->date('expense_date');
                $table->text('description')->nullable();
                $table->string('receipt_path')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
                $table->text('manager_notes')->nullable();
                $table->timestamps();
                
                // Indexes for performance
                $table->index(['employee_id']);
                $table->index(['status']);
                $table->index(['expense_date']);
            });
        }

        // Create claim_types table if it doesn't exist
        if (!Schema::hasTable('claim_types')) {
            Schema::create('claim_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->decimal('max_amount', 10, 2)->nullable();
                $table->boolean('requires_receipt')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['is_active']);
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
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('shift_types');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('employees');
    }
};
