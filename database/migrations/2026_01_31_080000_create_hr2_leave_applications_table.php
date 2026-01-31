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
        Schema::create('hr2_leave_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hr2_id')->unique()->comment('Original ID from HR2 API');
            $table->string('employee_id', 50)->index()->comment('Employee ID from HR2');
            $table->string('leave_id', 50)->nullable()->comment('Leave reference ID from HR2');
            $table->datetime('application_date')->nullable();
            $table->string('leave_type', 100)->nullable();
            $table->integer('leave_days')->default(0);
            $table->integer('days_requested')->default(0);
            $table->string('status', 50)->default('Pending')->index();
            $table->text('reason')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('contact_info', 100)->nullable();
            $table->datetime('applied_date')->nullable();
            $table->string('approved_by', 100)->nullable();
            $table->datetime('approved_date')->nullable();
            $table->text('remarks')->nullable();
            $table->datetime('hr2_created_at')->nullable()->comment('Original created_at from HR2');
            $table->datetime('hr2_updated_at')->nullable()->comment('Original updated_at from HR2');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr2_leave_applications');
    }
};
