<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Shift Types table (Shift templates)
     */
    public function up(): void
    {
        Schema::dropIfExists('shift_types');
        Schema::create('shift_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->time('default_start_time');
            $table->time('default_end_time');
            $table->integer('break_duration')->default(0); // minutes
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->string('color_code', 7)->default('#007bff');
            $table->enum('type', ['day', 'night', 'swing', 'split', 'rotating'])->default('day');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active']);
            $table->index(['type']);
            $table->unique(['code']);
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
