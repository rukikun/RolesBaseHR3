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
        Schema::table('ai_generated_timesheets', function (Blueprint $table) {
            // Approval tracking
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            
            // Rejection tracking
            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('rejected_at');
            $table->text('rejection_reason')->nullable()->after('rejected_by');
            
            // Payroll tracking
            $table->timestamp('sent_to_payroll_at')->nullable()->after('rejection_reason');
            $table->unsignedBigInteger('sent_by')->nullable()->after('sent_to_payroll_at');
            
            // Add indexes for better performance
            $table->index('status');
            $table->index('approved_at');
            $table->index('sent_to_payroll_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_generated_timesheets', function (Blueprint $table) {
            $table->dropColumn([
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'rejection_reason',
                'sent_to_payroll_at',
                'sent_by'
            ]);
        });
    }
};