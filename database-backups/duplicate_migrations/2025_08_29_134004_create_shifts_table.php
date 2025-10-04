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
            $table->date('date'); // Changed from shift_date to date
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_duration')->default(60);
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();
            
            // Indexes
            $table->index(['employee_id', 'date']);
            $table->index('date');
            $table->index('status');
            $table->unique(['employee_id', 'date']); // Prevent double booking
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
