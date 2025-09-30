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
        Schema::create('shift_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('request_type', ['change', 'swap', 'overtime', 'time_off']);
            $table->foreignId('current_shift_id')->nullable()->constrained('shifts')->onDelete('cascade');
            $table->foreignId('requested_shift_id')->nullable()->constrained('shifts')->onDelete('cascade');
            $table->date('requested_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['employee_id', 'status']);
            $table->index('status');
            $table->index('requested_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_requests');
    }
};
