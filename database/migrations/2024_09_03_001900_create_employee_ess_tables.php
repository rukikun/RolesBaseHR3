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
        // Employee notifications table
        if (!Schema::hasTable('employee_notifications')) {
            Schema::create('employee_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('title');
                $table->text('message');
                $table->enum('type', ['info', 'warning', 'success', 'danger'])->default('info');
                $table->timestamp('sent_at');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->index(['employee_id', 'read_at']);
            });
        }

        // Employee requests table
        if (!Schema::hasTable('employee_requests')) {
            Schema::create('employee_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('request_type');
                $table->text('reason');
                $table->date('requested_date')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected', 'processing'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->unsignedBigInteger('processed_by')->nullable();
                $table->timestamps();
                
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
                $table->index(['employee_id', 'status']);
            });
        }

        // Training programs table
        if (!Schema::hasTable('training_programs')) {
            Schema::create('training_programs', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description');
                $table->enum('type', ['mandatory', 'optional', 'certification'])->default('optional');
                $table->integer('duration_hours')->default(0);
                $table->enum('delivery_mode', ['online', 'classroom', 'hybrid'])->default('online');
                $table->decimal('cost', 10, 2)->default(0);
                $table->string('provider')->nullable();
                $table->json('prerequisites')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['type', 'is_active']);
            });
        }

        // Employee trainings table
        if (!Schema::hasTable('employee_trainings')) {
            Schema::create('employee_trainings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('training_id');
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled'])->default('assigned');
                $table->decimal('progress_percentage', 5, 2)->default(0);
                $table->decimal('score', 5, 2)->nullable();
                $table->text('completion_notes')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedBigInteger('assigned_by');
                $table->timestamps();
                
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->foreign('training_id')->references('id')->on('training_programs')->onDelete('cascade');
                $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
                $table->index(['employee_id', 'status']);
                $table->unique(['employee_id', 'training_id']);
            });
        }

        // Competency assessments table
        if (!Schema::hasTable('competency_assessments')) {
            Schema::create('competency_assessments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('competency_name');
                $table->text('description');
                $table->decimal('target_score', 5, 2)->default(80);
                $table->decimal('current_score', 5, 2)->default(0);
                $table->decimal('score', 5, 2)->default(0);
                $table->date('assessment_date');
                $table->date('next_assessment_date')->nullable();
                $table->enum('status', ['pending', 'in_progress', 'completed', 'overdue'])->default('pending');
                $table->text('assessor_notes')->nullable();
                $table->unsignedBigInteger('assessed_by')->nullable();
                $table->timestamps();
                
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->foreign('assessed_by')->references('id')->on('users')->onDelete('set null');
                $table->index(['employee_id', 'status']);
            });
        }

        // Payslips table
        if (!Schema::hasTable('payslips')) {
            Schema::create('payslips', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->date('pay_period_start');
                $table->date('pay_period_end');
                $table->decimal('basic_salary', 10, 2);
                $table->decimal('overtime_pay', 10, 2)->default(0);
                $table->decimal('allowances', 10, 2)->default(0);
                $table->decimal('bonuses', 10, 2)->default(0);
                $table->decimal('gross_pay', 10, 2);
                $table->decimal('tax_deduction', 10, 2)->default(0);
                $table->decimal('sss_deduction', 10, 2)->default(0);
                $table->decimal('philhealth_deduction', 10, 2)->default(0);
                $table->decimal('pagibig_deduction', 10, 2)->default(0);
                $table->decimal('other_deductions', 10, 2)->default(0);
                $table->decimal('total_deductions', 10, 2)->default(0);
                $table->decimal('net_pay', 10, 2);
                $table->string('file_path')->nullable();
                $table->enum('status', ['draft', 'finalized', 'sent'])->default('draft');
                $table->timestamp('generated_at');
                $table->unsignedBigInteger('generated_by');
                $table->timestamps();
                
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->foreign('generated_by')->references('id')->on('users')->onDelete('cascade');
                $table->index(['employee_id', 'pay_period_end']);
                $table->unique(['employee_id', 'pay_period_start', 'pay_period_end']);
            });
        }

        // Employee documents table
        if (!Schema::hasTable('employee_documents')) {
            Schema::create('employee_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('document_type');
                $table->string('document_name');
                $table->string('file_path');
                $table->string('file_size')->nullable();
                $table->string('mime_type')->nullable();
                $table->date('expiry_date')->nullable();
                $table->boolean('is_confidential')->default(false);
                $table->text('description')->nullable();
                $table->unsignedBigInteger('uploaded_by');
                $table->timestamps();
                
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
                $table->index(['employee_id', 'document_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('competency_assessments');
        Schema::dropIfExists('employee_trainings');
        Schema::dropIfExists('training_programs');
        Schema::dropIfExists('employee_requests');
        Schema::dropIfExists('employee_notifications');
    }
};
