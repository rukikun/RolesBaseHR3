<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->integer('year');
            $table->integer('allocated_days')->default(0);
            $table->integer('used_days')->default(0);
            $table->integer('remaining_days')->default(0);
            $table->integer('carried_forward')->default(0);
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            $table->unique(['employee_id', 'leave_type_id', 'year'], 'unique_employee_leave_year');
            $table->index(['employee_id', 'year'], 'idx_employee_year');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
