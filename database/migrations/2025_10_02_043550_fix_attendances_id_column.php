<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix the attendances table ID column to ensure it's auto-increment
        DB::statement('ALTER TABLE attendances MODIFY COLUMN id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY');
        
        // Also ensure the table has proper structure
        Schema::table('attendances', function (Blueprint $table) {
            // Make sure all required columns exist with proper defaults
            if (!Schema::hasColumn('attendances', 'total_hours')) {
                $table->decimal('total_hours', 5, 2)->default(0)->after('break_end_time');
            }
            if (!Schema::hasColumn('attendances', 'overtime_hours')) {
                $table->decimal('overtime_hours', 5, 2)->default(0)->after('total_hours');
            }
            if (!Schema::hasColumn('attendances', 'location')) {
                $table->string('location')->nullable()->after('status');
            }
            if (!Schema::hasColumn('attendances', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('location');
            }
            if (!Schema::hasColumn('attendances', 'notes')) {
                $table->text('notes')->nullable()->after('ip_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't reverse the ID fix as it might break the table
    }
};
