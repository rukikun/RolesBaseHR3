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
        Schema::table('employee_activities', function (Blueprint $table) {
            // Drop and recreate the performed_at column without useCurrent()
            $table->dropColumn('performed_at');
        });
        
        Schema::table('employee_activities', function (Blueprint $table) {
            // Add performed_at as a nullable timestamp
            $table->timestamp('performed_at')->nullable()->after('metadata');
            $table->index('performed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_activities', function (Blueprint $table) {
            $table->dropColumn('performed_at');
        });
        
        Schema::table('employee_activities', function (Blueprint $table) {
            $table->timestamp('performed_at')->useCurrent()->after('metadata');
        });
    }
};
