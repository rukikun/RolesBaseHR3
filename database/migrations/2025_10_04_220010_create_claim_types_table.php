<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Claim Types table
     */
    public function up(): void
    {
        Schema::dropIfExists('claim_types');
        Schema::create('claim_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 10);
            $table->text('description')->nullable();
            $table->decimal('max_amount', 10, 2)->nullable();
            $table->boolean('requires_attachment')->default(false);
            $table->boolean('auto_approve')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active']);
            $table->unique(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_types');
    }
};
