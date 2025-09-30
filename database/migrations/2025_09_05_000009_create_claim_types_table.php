<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('max_amount', 10, 2)->nullable();
            $table->boolean('requires_receipt')->default(true);
            $table->boolean('approval_required')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('status', 'idx_status');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('claim_types');
    }
};
