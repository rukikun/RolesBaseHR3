<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First ensure the shift_types table exists
        if (!Schema::hasTable('shift_types')) {
            Schema::create('shift_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->time('default_start_time');
                $table->time('default_end_time');
                $table->string('color_code')->default('#007bff');
                $table->enum('type', ['regular', 'overtime', 'overnight'])->default('regular');
                $table->integer('break_duration')->default(30);
                $table->decimal('hourly_rate', 8, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Clear existing data and insert fresh data
        DB::table('shift_types')->truncate();
        
        // Insert default shift types
        DB::table('shift_types')->insert([
            [
                'name' => 'Morning Shift',
                'description' => 'Standard morning work shift',
                'default_start_time' => '08:00:00',
                'default_end_time' => '16:00:00',
                'color_code' => '#3B82F6',
                'type' => 'regular',
                'break_duration' => 30,
                'hourly_rate' => 25.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Evening Shift',
                'description' => 'Standard evening work shift',
                'default_start_time' => '16:00:00',
                'default_end_time' => '00:00:00',
                'color_code' => '#F59E0B',
                'type' => 'regular',
                'break_duration' => 30,
                'hourly_rate' => 27.50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Night Shift',
                'description' => 'Overnight work shift',
                'default_start_time' => '00:00:00',
                'default_end_time' => '08:00:00',
                'color_code' => '#8B5CF6',
                'type' => 'overnight',
                'break_duration' => 30,
                'hourly_rate' => 30.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Weekend Day',
                'description' => 'Weekend day shift',
                'default_start_time' => '09:00:00',
                'default_end_time' => '17:00:00',
                'color_code' => '#10B981',
                'type' => 'regular',
                'break_duration' => 30,
                'hourly_rate' => 28.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Weekend Night',
                'description' => 'Weekend night shift',
                'default_start_time' => '22:00:00',
                'default_end_time' => '06:00:00',
                'color_code' => '#EF4444',
                'type' => 'overnight',
                'break_duration' => 30,
                'hourly_rate' => 32.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_types');
    }
};
