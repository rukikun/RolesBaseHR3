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
        Schema::create('validated_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('claim_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('employee_name')->nullable();
            $table->string('claim_type')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('claim_date')->nullable();
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['validated', 'sent_to_payroll', 'processed'])->default('validated');
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('sent_to_payroll_at')->nullable();
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('claim_id');
            $table->index('employee_id');
            $table->index('status');
            $table->index(['status', 'validated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validated_attachments');
    }
};
