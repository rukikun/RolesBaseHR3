<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('shift_type_id');
            $table->date('requested_date');
            $table->time('preferred_start_time')->nullable();
            $table->time('preferred_end_time')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('shift_type_id')->references('id')->on('shift_types')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
            $table->index(['employee_id', 'status'], 'idx_employee_status');
            $table->index(['requested_date', 'status'], 'idx_date_status');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('shift_requests');
    }
};
