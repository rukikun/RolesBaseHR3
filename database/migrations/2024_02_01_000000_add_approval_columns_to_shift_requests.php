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
        Schema::table('shift_requests', function (Blueprint $table) {
            // Add approval columns if they don't exist
            if (!Schema::hasColumn('shift_requests', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
                $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('shift_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at']);
        });
    }
};
