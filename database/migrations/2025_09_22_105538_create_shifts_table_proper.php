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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('shift_type_id')->constrained('shift_types')->onDelete('cascade');
            $table->date('shift_date');
            $table->date('date')->nullable(); // Keep for backward compatibility
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->default('Main Office');
            $table->integer('break_duration')->nullable()->comment('Break duration in minutes');
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['employee_id', 'shift_date']);
            $table->index(['shift_type_id', 'shift_date']);
            $table->index('shift_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
