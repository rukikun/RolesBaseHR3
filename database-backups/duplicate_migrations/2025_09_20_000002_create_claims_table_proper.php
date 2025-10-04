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
        // Create claims table with proper structure
        Schema::create('claims', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('claim_type_id');
            $table->decimal('amount', 10, 2);
            $table->date('claim_date');
            $table->text('description');
            $table->string('receipt_path')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['employee_id', 'status']);
            $table->index(['claim_type_id']);
            $table->index(['status']);
            $table->index(['claim_date']);
        });

        // Insert sample data for testing
        DB::table('claims')->insert([
            [
                'employee_id' => 42,
                'claim_type_id' => 1,
                'amount' => 150.00,
                'claim_date' => '2025-09-15',
                'description' => 'Business lunch with client',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 43,
                'claim_type_id' => 2,
                'amount' => 75.50,
                'claim_date' => '2025-09-18',
                'description' => 'Medical checkup expenses',
                'status' => 'approved',
                'approved_by' => 42,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
