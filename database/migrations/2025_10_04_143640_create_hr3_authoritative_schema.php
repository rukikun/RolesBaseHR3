<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - HR3 System Authoritative Schema
     */
    public function up(): void
    {
        // Users table (Laravel default with HR extensions)
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('phone')->nullable();
                $table->string('profile_picture')->nullable();
                $table->enum('role', ['admin', 'hr', 'employee'])->default('employee');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // Employees table (Core HR entity)
        Schema::dropIfExists('employees');
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number')->unique()->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('position');
            $table->string('department');
            $table->date('hire_date');
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->enum('online_status', ['online', 'offline', 'away'])->default('offline');
            $table->timestamp('last_activity')->nullable();
            $table->string('password')->nullable(); // For ESS login
            $table->string('profile_picture')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['email']);
            $table->index(['status']);
            $table->index(['department']);
            $table->index(['online_status']);
        });

        // Time Entries table (Payroll/Timesheet management)
        Schema::dropIfExists('time_entries');
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('work_date');
            $table->time('clock_in_time')->nullable();
            $table->time('clock_out_time')->nullable();
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->integer('break_duration')->default(0); // minutes
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('employees');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['employee_id', 'work_date']);
            $table->index(['status']);
            $table->index(['work_date']);
        });

        // Attendances table (ESS Clock-in/out tracking)
        Schema::dropIfExists('attendances');
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date');
            $table->datetime('clock_in_time')->nullable();
            $table->datetime('clock_out_time')->nullable();
            $table->datetime('break_start_time')->nullable();
            $table->datetime('break_end_time')->nullable();
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'on_break', 'clocked_out'])->default('present');
            $table->string('location')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes and constraints
            $table->index(['employee_id', 'date']);
            $table->index(['date']);
            $table->index(['status']);
            $table->unique(['employee_id', 'date']); // One attendance record per employee per day
        });

        // Shift Types table (Shift templates)
        Schema::dropIfExists('shift_types');
        Schema::create('shift_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->time('default_start_time');
            $table->time('default_end_time');
            $table->integer('break_duration')->default(0); // minutes
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->string('color_code', 7)->default('#007bff');
            $table->enum('type', ['day', 'night', 'swing', 'split', 'rotating'])->default('day');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active']);
            $table->index(['type']);
            $table->unique(['code']);
        });

        // Shifts table (Employee shift assignments)
        Schema::dropIfExists('shifts');
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('shift_type_id')->nullable()->constrained('shift_types')->onDelete('set null');
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable();
            $table->integer('break_duration')->default(0); // minutes
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'shift_date']);
            $table->index(['shift_date']);
            $table->index(['status']);
        });

        // Shift Requests table (Employee shift change requests)
        Schema::dropIfExists('shift_requests');
        Schema::create('shift_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('current_shift_id')->nullable()->constrained('shifts')->onDelete('cascade');
            $table->foreignId('requested_shift_type_id')->nullable()->constrained('shift_types')->onDelete('set null');
            $table->date('requested_date');
            $table->time('requested_start_time')->nullable();
            $table->time('requested_end_time')->nullable();
            $table->text('reason');
            $table->enum('request_type', ['swap', 'change', 'cancel'])->default('change');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('employees');
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'status']);
            $table->index(['status']);
            $table->index(['requested_date']);
        });

        // Leave Types table
        Schema::dropIfExists('leave_types');
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 10)->nullable();
            $table->text('description')->nullable();
            $table->integer('days_allowed')->default(0);
            $table->integer('max_days_per_year')->default(0);
            $table->boolean('carry_forward')->default(false);
            $table->boolean('requires_approval')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active']);
            $table->index(['status']);
        });

        // Leave Requests table
        Schema::dropIfExists('leave_requests');
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_requested');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('employees');
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'status']);
            $table->index(['status']);
        });

        // Claim Types table
        Schema::dropIfExists('claim_types');
        Schema::create('claim_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 10);
            $table->text('description')->nullable();
            $table->decimal('max_amount', 10, 2)->nullable();
            $table->boolean('requires_attachment')->default(false);
            $table->boolean('auto_approve')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active']);
            $table->unique(['code']);
        });

        // Claims table
        Schema::dropIfExists('claims');
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('claim_type_id')->constrained('claim_types')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->date('claim_date');
            $table->text('description');
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('employees');
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'status']);
            $table->index(['status']);
        });

        // AI Generated Timesheets table (for AI timesheet feature)
        Schema::dropIfExists('ai_generated_timesheets');
        Schema::create('ai_generated_timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('employee_name');
            $table->string('department')->nullable();
            $table->date('week_start_date');
            $table->json('weekly_data')->nullable();
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->json('ai_insights')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('employees');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'week_start_date']);
            $table->index(['status', 'generated_at']);
            $table->unique(['employee_id', 'week_start_date']); // One AI timesheet per employee per week
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generated_timesheets');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('claim_types');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('shift_requests');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('shift_types');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('users');
    }
};