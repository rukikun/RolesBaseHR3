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
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->foreignId('created_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['employee_id', 'date']);
            $table->index(['shift_type_id', 'date']);
            $table->index('status');
            $table->index('date');
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
