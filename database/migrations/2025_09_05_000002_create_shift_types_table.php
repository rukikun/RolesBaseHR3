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
        Schema::create('shift_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->time('default_start_time');
            $table->time('default_end_time');
            $table->integer('break_duration')->default(0); // in minutes
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->string('color_code', 7)->default('#007bff');
            $table->enum('type', ['day', 'night', 'swing', 'split', 'rotating'])->default('day');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->unique('name', 'unique_name');
            $table->unique('code', 'unique_code');
            $table->index('is_active', 'idx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_types');
    }
};
