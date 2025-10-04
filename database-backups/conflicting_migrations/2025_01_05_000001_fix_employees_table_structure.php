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
        // Drop the employees table if it exists and recreate with correct structure
        Schema::dropIfExists('employees');
        
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('position', 100);
            $table->string('department', 100);
            $table->date('hire_date');
            $table->decimal('salary', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->enum('online_status', ['online', 'offline'])->default('offline');
            $table->timestamp('last_activity')->nullable();
            $table->string('password')->nullable(); // For ESS login
            $table->string('profile_picture')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['department', 'status']);
            $table->index('hire_date');
            $table->index('status');
            $table->index('online_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
