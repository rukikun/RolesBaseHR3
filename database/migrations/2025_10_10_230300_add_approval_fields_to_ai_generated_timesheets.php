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
        // Check if table exists first
        if (!Schema::hasTable('ai_generated_timesheets')) {
            return; // Skip if table doesn't exist
        }
        
        Schema::table('ai_generated_timesheets', function (Blueprint $table) {
            // Add approval/rejection tracking fields only if they don't exist
            $columns = Schema::getColumnListing('ai_generated_timesheets');
            
            if (!in_array('approved_at', $columns)) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!in_array('rejected_at', $columns)) {
                $table->timestamp('rejected_at')->nullable();
            }
            if (!in_array('rejection_reason', $columns)) {
                $table->text('rejection_reason')->nullable();
            }
            if (!in_array('approved_by', $columns)) {
                $table->unsignedBigInteger('approved_by')->nullable();
            }
            if (!in_array('rejected_by', $columns)) {
                $table->unsignedBigInteger('rejected_by')->nullable();
            }
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
                'rejected_at', 
                'rejection_reason',
                'approved_by',
                'rejected_by'
            ]);
        });
    }
};
