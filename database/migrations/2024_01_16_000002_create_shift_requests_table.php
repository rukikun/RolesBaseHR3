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
            $table->enum('request_type', ['change', 'swap', 'overtime', 'time_off', 'shift_change'])->default('shift_change');
            $table->foreignId('current_shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->foreignId('requested_shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->foreignId('current_shift_type_id')->nullable()->constrained('shift_types')->onDelete('set null');
            $table->foreignId('shift_type_id')->nullable()->constrained('shift_types')->onDelete('set null');
            $table->date('requested_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id']);
            $table->index(['status']);
            $table->index(['requested_date']);
            $table->index(['request_type']);
            
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
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
