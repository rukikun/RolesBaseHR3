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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('position', 100);
            $table->string('department', 100);
            $table->date('hire_date');
            $table->decimal('salary', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['department', 'status']);
            $table->index('hire_date');
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
