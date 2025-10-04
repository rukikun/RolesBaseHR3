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
            $table->unsignedBigInteger('employee_id');
            $table->enum('request_type', ['shift_change', 'time_off', 'overtime', 'swap']);
            $table->unsignedBigInteger('current_shift_id')->nullable();
            $table->unsignedBigInteger('requested_shift_id')->nullable();
            $table->date('requested_date');
            $table->time('requested_start_time')->nullable();
            $table->time('requested_end_time')->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('current_shift_id')->references('id')->on('shifts')->onDelete('set null');
            $table->foreign('requested_shift_id')->references('id')->on('shifts')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');

            // Indexes
            $table->index('employee_id');
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
