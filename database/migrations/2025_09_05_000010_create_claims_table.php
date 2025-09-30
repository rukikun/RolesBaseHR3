<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('claim_type_id');
            $table->decimal('amount', 10, 2);
            $table->date('claim_date');
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('claim_type_id')->references('id')->on('claim_types')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
            $table->index(['employee_id', 'status'], 'idx_employee_status');
            $table->index('claim_type_id', 'idx_claim_type');
            $table->index('claim_date', 'idx_claim_date');
            $table->index(['claim_date', 'status'], 'idx_claims_date_status');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
