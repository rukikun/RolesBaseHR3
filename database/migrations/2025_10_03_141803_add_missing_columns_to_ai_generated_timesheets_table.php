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
            // Add columns if they don't exist
            if (!Schema::hasColumn('ai_generated_timesheets', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('ai_generated_timesheets', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('ai_generated_timesheets', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
            if (!Schema::hasColumn('ai_generated_timesheets', 'generated_at')) {
                $table->timestamp('generated_at')->nullable()->after('ai_insights');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_generated_timesheets', function (Blueprint $table) {
            $table->dropColumn(['rejected_by', 'rejected_at', 'rejection_reason', 'generated_at']);
        });
    }
};
