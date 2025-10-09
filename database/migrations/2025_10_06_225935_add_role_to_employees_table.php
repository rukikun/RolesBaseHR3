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
        Schema::table('employees', function (Blueprint $table) {
            // Drop the old role column if it exists
            if (Schema::hasColumn('employees', 'role')) {
                $table->dropColumn('role');
            }
            
            // Add the new role column with updated roles
            $table->enum('role', ['employee', 'super_admin', 'admin', 'hr_manager', 'hr_scheduler'])->default('employee')->after('status');
            
            // Add remember_token if it doesn't exist
            if (!Schema::hasColumn('employees', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['role', 'remember_token']);
        });
    }
};
