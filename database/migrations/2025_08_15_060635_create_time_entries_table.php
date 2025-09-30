<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('time_entries', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->constrained();
        $table->date('date');
        $table->time('clock_in');
        $table->time('clock_out')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }

};
