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
        // Ensure employees table exists with required columns
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('position')->nullable();
                $table->string('department')->nullable();
                $table->date('hire_date')->nullable();
                $table->decimal('salary', 10, 2)->nullable();
                $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
                $table->enum('online_status', ['online', 'offline', 'away'])->default('offline');
                $table->timestamp('last_activity')->nullable();
                $table->string('password')->nullable();
                $table->string('profile_picture')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        } else {
            // Add missing columns to existing employees table
            Schema::table('employees', function (Blueprint $table) {
                if (!Schema::hasColumn('employees', 'online_status')) {
                    $table->enum('online_status', ['online', 'offline', 'away'])->default('offline');
                }
                if (!Schema::hasColumn('employees', 'last_activity')) {
                    $table->timestamp('last_activity')->nullable();
                }
            });
        }

        // Ensure time_entries table exists
        if (!Schema::hasTable('time_entries')) {
            Schema::create('time_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->date('work_date');
                $table->time('clock_in_time')->nullable();
                $table->time('clock_out_time')->nullable();
                $table->decimal('hours_worked', 5, 2)->nullable();
                $table->decimal('overtime_hours', 5, 2)->default(0);
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('notes')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('employees');
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                
                $table->index(['employee_id', 'work_date']);
            });
        }

        // Ensure shift_types table exists
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

        // Ensure shifts table exists
        if (!Schema::hasTable('shifts')) {
            Schema::create('shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('shift_type_id')->nullable()->constrained('shift_types')->onDelete('set null');
                $table->date('shift_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('location')->nullable();
                $table->integer('break_duration')->default(0); // in minutes
                $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['employee_id', 'shift_date']);
            });
        }

        // Ensure leave_types table exists
        if (!Schema::hasTable('leave_types')) {
            Schema::create('leave_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('days_per_year')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Ensure leave_requests table exists
        if (!Schema::hasTable('leave_requests')) {
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
            });
        }

        // Ensure claim_types table exists
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

        // Ensure claims table exists
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
                $table->foreignId('approved_by')->nullable()->constrained('employees');
                $table->timestamp('approved_at')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamps();
                
                $table->index(['employee_id', 'status']);
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
        
        // Don't drop employees table as it might be used elsewhere
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (Schema::hasColumn('employees', 'online_status')) {
                    $table->dropColumn('online_status');
                }
                if (Schema::hasColumn('employees', 'last_activity')) {
                    $table->dropColumn('last_activity');
                }
            });
        }
    }
};
