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
            $table->enum('request_type', ['change', 'swap', 'overtime', 'time_off'])->default('change');
            $table->foreignId('current_shift_type_id')->nullable()->constrained('shift_types')->onDelete('set null');
            $table->foreignId('requested_shift_type_id')->constrained('shift_types')->onDelete('cascade');
            $table->date('request_date');
            $table->date('requested_date');
            $table->time('requested_start_time')->nullable();
            $table->time('requested_end_time')->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id']);
            $table->index(['status']);
            $table->index(['request_date']);
            $table->index(['requested_date']);
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
