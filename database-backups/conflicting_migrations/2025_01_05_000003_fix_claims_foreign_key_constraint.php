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
        // First, check if claims table exists
        if (!Schema::hasTable('claims')) {
            // Create claims table if it doesn't exist
            Schema::create('claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('claim_type_id')->constrained('claim_types')->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->date('claim_date');
                $table->text('description');
                $table->string('receipt_path')->nullable();
                $table->string('attachment_path')->nullable(); // Alternative field name
                $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
                $table->timestamps();
                
                // Indexes
                $table->index(['employee_id', 'status']);
                $table->index('claim_date');
            });
        } else {
            // Fix existing foreign key constraint
            Schema::table('claims', function (Blueprint $table) {
                // Drop existing foreign key if it exists
                try {
                    $table->dropForeign(['approved_by']);
                } catch (Exception $e) {
                    // Foreign key might not exist
                }
                
                // Add attachment_path column if it doesn't exist
                if (!Schema::hasColumn('claims', 'attachment_path')) {
                    $table->string('attachment_path')->nullable()->after('receipt_path');
                }
            });
            
            // Re-add foreign key with proper constraint
            Schema::table('claims', function (Blueprint $table) {
                $table->foreign('approved_by')
                      ->references('id')
                      ->on('employees')
                      ->onDelete('set null');
            });
        }
        
        // Ensure we have valid employee IDs for any existing approved_by values
        DB::statement("
            UPDATE claims 
            SET approved_by = NULL 
            WHERE approved_by IS NOT NULL 
            AND approved_by NOT IN (SELECT id FROM employees)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('claims')) {
            Schema::table('claims', function (Blueprint $table) {
                try {
                    $table->dropForeign(['approved_by']);
                } catch (Exception $e) {
                    // Foreign key might not exist
                }
                
                if (Schema::hasColumn('claims', 'attachment_path')) {
                    $table->dropColumn('attachment_path');
                }
            });
        }
    }
};
