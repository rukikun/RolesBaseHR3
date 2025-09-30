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
        // Ensure employees table has correct structure for authentication
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('position')->nullable();
                $table->string('department')->nullable();
                $table->date('hire_date')->nullable();
                $table->decimal('salary', 10, 2)->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->enum('online_status', ['online', 'offline'])->default('offline');
                $table->timestamp('last_activity')->nullable();
                $table->string('password');
                $table->string('profile_picture')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        } else {
            // Add missing columns if table exists
            Schema::table('employees', function (Blueprint $table) {
                if (!Schema::hasColumn('employees', 'remember_token')) {
                    $table->rememberToken();
                }
                if (!Schema::hasColumn('employees', 'online_status')) {
                    $table->enum('online_status', ['online', 'offline'])->default('offline');
                }
                if (!Schema::hasColumn('employees', 'last_activity')) {
                    $table->timestamp('last_activity')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the table, just remove added columns
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (Schema::hasColumn('employees', 'remember_token')) {
                    $table->dropColumn('remember_token');
                }
            });
        }
    }
};
