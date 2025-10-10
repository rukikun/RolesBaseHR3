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
        // Verify and update attendance table structure
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->date('date');
                $table->time('clock_in_time')->nullable();
                $table->time('clock_out_time')->nullable();
                $table->string('status')->default('present');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->index(['employee_id', 'date']);
            });
        } else {
            // Ensure required columns exist
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->after('id');
                }
                if (!Schema::hasColumn('attendances', 'date')) {
                    $table->date('date')->after('employee_id');
                }
                if (!Schema::hasColumn('attendances', 'clock_in_time')) {
                    $table->time('clock_in_time')->nullable()->after('date');
                }
                if (!Schema::hasColumn('attendances', 'clock_out_time')) {
                    $table->time('clock_out_time')->nullable()->after('clock_in_time');
                }
                if (!Schema::hasColumn('attendances', 'status')) {
                    $table->string('status')->default('present')->after('clock_out_time');
                }
                if (!Schema::hasColumn('attendances', 'notes')) {
                    $table->text('notes')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the table as it might contain important data
        // Schema::dropIfExists('attendances');
    }
};
