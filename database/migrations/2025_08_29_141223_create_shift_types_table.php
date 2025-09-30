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
            $table->string('name');
            $table->enum('type', ['day', 'night', 'swing', 'split', 'rotating'])->default('day');
            $table->time('default_start_time');
            $table->time('default_end_time');
            $table->integer('break_duration')->default(30); // minutes
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('color_code', 7)->default('#007bff');
            $table->timestamps();
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
